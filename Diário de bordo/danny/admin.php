<?php
session_start();

require_once __DIR__ . '/src/bootstrap.php';

use Danny\Database;

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

function e(mixed $value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function statusClass(string $status): string
{
  return match ($status) {
    'CONFIRMADO' => 'status--confirmed',
    'CONCLUIDO' => 'status--done',
    'PENDENTE' => 'status--waiting',
    'RECUSADO', 'CANCELADO' => 'status--new',
    default => 'status--waiting',
  };
}

function formatDateBr(?string $date): string
{
  return $date ? date('d/m/Y', strtotime($date)) : '-';
}

function formatTimeBr(?string $time): string
{
  return $time ? substr($time, 0, 5) : '-';
}

function formatPeriodoAgendamento(?string $data, ?string $horaInicio, mixed $duracaoMinutos): string
{
  if (!$data || !$horaInicio) {
    return '-';
  }

  $duracao = (int) ($duracaoMinutos ?: 60);
  $inicio = new DateTime($data . ' ' . $horaInicio);
  $fim = clone $inicio;
  $fim->modify('+' . $duracao . ' minutes');

  $periodo = $inicio->format('H:i') . ' às ' . $fim->format('H:i');

  if ($fim->format('Y-m-d') !== $inicio->format('Y-m-d')) {
    $periodo .= ' do dia seguinte';
  }

  return $periodo;
}


function formatMoneyBr(mixed $value): string
{
  if ($value === null || $value === '') {
    return 'Não informado';
  }

  return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function onlyDigits(?string $value): string
{
  return preg_replace('/\D+/', '', (string) $value);
}

function whatsappLink(?string $telefone, string $cliente): ?string
{
  $digits = onlyDigits($telefone);

  if ($digits === '' || strlen($digits) < 10) {
    return null;
  }

  if (!str_starts_with($digits, '55')) {
    $digits = '55' . $digits;
  }

  $mensagem = rawurlencode('Olá ' . $cliente . ', tudo bem? Vim responder sua solicitação de agendamento.');

  return 'https://wa.me/' . $digits . '?text=' . $mensagem;
}

function colunaExiste(PDO $pdo, string $tabela, string $coluna): bool
{
  $stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = :tabela
      AND COLUMN_NAME = :coluna
  ");

  $stmt->execute([
    ':tabela' => $tabela,
    ':coluna' => $coluna,
  ]);

  return (int) $stmt->fetchColumn() > 0;
}


$erroBanco = null;
$nichos = [];
$fotografos = [];
$tiposServico = [];
$cenarios = [];
$agendamentos = [];
$pendentes = [];
$metricas = [
  'hoje' => 0,
  'historico' => 0,
  'pendentes' => 0,
];

$sqlAgendamentosBase = "
  SELECT
    a.id,
    u.nome AS cliente,
    u.email AS email_cliente,
    u.telefone AS telefone_cliente,
    f.nome AS fotografo,
    COALESCE(n.nome, 'Não informado') AS nicho,
    ts.nome AS tipo_servico,
    ts.duracao_minutos,
    c.nome AS cenario,
    a.data,
    a.hora_inicio,
    ADDTIME(a.hora_inicio, SEC_TO_TIME(ts.duracao_minutos * 60)) AS hora_fim_calculada,
    a.observacao,
    a.valor,
    a.status
  FROM agendamentos a
  INNER JOIN admins u ON u.id = a.admin_id
  INNER JOIN fotografos f ON f.id = a.fotografo_id
  INNER JOIN tipos_servico ts ON ts.id = a.tipo_servico_id
  LEFT JOIN nichos n ON n.id = ts.nicho_id
  LEFT JOIN cenarios c ON c.id = a.cenario_id
";

try {
  $pdo = Database::connection();

  $telefoneClienteSql = colunaExiste($pdo, 'admins', 'telefone')
    ? "u.telefone AS telefone_cliente,"
    : "NULL AS telefone_cliente,";

  $sqlAgendamentosBase = str_replace("u.telefone AS telefone_cliente,", $telefoneClienteSql, $sqlAgendamentosBase);

  $nichos = $pdo->query("SELECT id, nome FROM nichos ORDER BY nome")->fetchAll();
  $fotografos = $pdo->query("SELECT id, nome FROM fotografos ORDER BY nome")->fetchAll();

  $tiposServico = $pdo->query("
    SELECT
      ts.id,
      ts.nome,
      ts.duracao_minutos,
      ts.exige_cenario,
      COALESCE(n.nome, 'Sem nicho') AS nicho
    FROM tipos_servico ts
    LEFT JOIN nichos n ON n.id = ts.nicho_id
    ORDER BY n.nome, ts.nome
  ")->fetchAll();

  $cenarios = $pdo->query("
    SELECT
      c.id,
      c.nome,
      c.mes,
      c.ano,
      COALESCE(n.nome, 'Sem nicho') AS nicho
    FROM cenarios c
    LEFT JOIN nichos n ON n.id = c.nicho_id
    ORDER BY n.nome, c.nome
  ")->fetchAll();

  $agendamentos = $pdo->query($sqlAgendamentosBase . "
    WHERE a.status = 'CONFIRMADO'
    ORDER BY a.data ASC, a.hora_inicio ASC
    LIMIT 50
  ")->fetchAll();


  $pendentes = $pdo->query($sqlAgendamentosBase . "
    WHERE a.status = 'PENDENTE'
    ORDER BY a.data ASC, a.hora_inicio ASC
    LIMIT 50
  ")->fetchAll();

  $metricas['hoje'] = (int) $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data = CURRENT_DATE AND status = 'CONFIRMADO'")->fetchColumn();
  $metricas['historico'] = (int) $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status IN ('CONCLUIDO', 'RECUSADO', 'CANCELADO')")->fetchColumn();
  $metricas['pendentes'] = (int) $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'PENDENTE'")->fetchColumn();
} catch (PDOException $e) {
  $erroBanco = $e->getMessage();
}

function mensagemErro(string $erro): string
{
  return match ($erro) {
    'campos' => 'Preencha todos os campos obrigatórios do atendimento.',
    'telefone' => 'Informe um telefone válido com DDD no formato (xx) xxxxx-xxxx.',
    'email' => 'Informe um e-mail válido para contato.',
    'banco' => 'Erro ao executar a ação no banco.',
    'status' => 'Status inválido.',
    'antecedencia' => 'Escolha uma data com pelo menos 7 dias de antecedência.',
    'horario_limite' => 'Não foi possível salvar o atendimento. O horário inicial deve estar dentro do período permitido de atendimento, entre 06:00 e 20:00.',
    'servico' => 'Tipo de serviço inválido.',
    'cenario_obrigatorio' => 'Este serviço exige cenário.',
    'cenario' => 'Cenário inválido.',
    'cenario_nicho' => 'O cenário não pertence ao mesmo nicho do serviço.',
    'cenario_mes' => 'O cenário não está disponível para o mês escolhido.',
    'cenario_ano' => 'O cenário não está disponível para o ano escolhido.',
    'disponibilidade' => 'Horário indisponível.',
    'conflito' => 'Já existe um agendamento nesse período para o fotógrafo escolhido.',
    default => 'Ocorreu um erro. Verifique os dados informados.',
  };
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Danny</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="admin-page">
  <div class="admin-shell">
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <main class="admin-main">
      <header class="admin-topbar">
        <div>
          <span class="label">Painel administrativo</span>
          <h1 class="admin-title">Agenda e ensaios.</h1>
        </div>
        <div class="admin-topbar__actions">
          <a class="btn btn--outline" href="historico.php">Histórico</a>
          <a class="btn btn--outline" href="servicos-cadastrados.php">Serviços cadastrados</a>
          <a class="btn btn--primary" href="#novo-agendamento">Cadastrar atendimento</a>
        </div>
      </header>

      <?php if ($erroBanco): ?>
        <section class="admin-section">
          <p style="color: red; font-weight: bold;">Erro ao buscar dados do banco: <?= e($erroBanco) ?></p>
        </section>
      <?php endif; ?>

      <?php if (isset($_GET['agendamento']) && $_GET['agendamento'] === 'sucesso'): ?>
        <p style="color: green; font-weight: bold;">Agendamento salvo com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['agendamento']) && $_GET['agendamento'] === 'atualizado'): ?>
        <p style="color: green; font-weight: bold;">Agendamento atualizado com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['agendamento']) && $_GET['agendamento'] === 'excluido'): ?>
        <p style="color: green; font-weight: bold;">Agendamento excluído com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['status']) && $_GET['status'] === 'sucesso'): ?>
        <p style="color: green; font-weight: bold;">Status atualizado com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['opcao']) && $_GET['opcao'] === 'sucesso'): ?>
        <p style="color: green; font-weight: bold;">Opção salva com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['opcao']) && $_GET['opcao'] === 'excluida'): ?>
        <p style="color: green; font-weight: bold;">Opção excluída com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <p style="color: red; font-weight: bold;"><?= e(mensagemErro((string) $_GET['erro'])) ?></p>
      <?php endif; ?>

      <section class="admin-metrics" aria-label="Resumo">
        <article class="metric-card">
          <span class="metric-card__label">Hoje</span>
          <strong class="metric-card__value"><?= e($metricas['hoje']) ?></strong>
          <span class="metric-card__note">ensaios confirmados</span>
        </article>
        <article class="metric-card">
          <span class="metric-card__label">Histórico</span>
          <strong class="metric-card__value"><?= e($metricas['historico']) ?></strong>
          <span class="metric-card__note">atendimentos finalizados ou encerrados</span>
        </article>
        <article class="metric-card">
          <span class="metric-card__label">Solicitações</span>
          <strong class="metric-card__value"><?= e($metricas['pendentes']) ?></strong>
          <span class="metric-card__note">aguardando confirmação</span>
        </article>
      </section>

      <section class="admin-section" id="agenda">
        <div class="admin-section__header">
          <div>
            <span class="label">Agenda</span>
            <h2 class="admin-section__title">Agendamentos do banco</h2>
          </div>
        </div>

        <div class="schedule-list">
          <?php if (!$agendamentos): ?>
            <p class="admin-empty">Nenhum agendamento ativo no momento.</p>
          <?php endif; ?>

          <?php foreach ($agendamentos as $agendamento): ?>
            <article class="schedule-item">
              <time class="schedule-item__time" datetime="<?= e($agendamento['data'] . 'T' . $agendamento['hora_inicio']) ?>">
                <span><?= e(formatDateBr($agendamento['data'])) ?></span>
                <?= e(formatPeriodoAgendamento($agendamento['data'], $agendamento['hora_inicio'], $agendamento['duracao_minutos'] ?? 60)) ?>
              </time>
              <div>
                <h3><?= e($agendamento['cliente']) ?></h3>
                <p>
                  <?= e($agendamento['tipo_servico']) ?> • <?= e($agendamento['nicho']) ?> •
                  Fotógrafo: <?= e($agendamento['fotografo']) ?> •
                  Cenário: <?= e($agendamento['cenario'] ?: 'Sem cenário') ?>
                </p>
                <p>Valor: <?= e(formatMoneyBr($agendamento['valor'] ?? null)) ?></p>
              </div>
              <span class="status <?= e(statusClass($agendamento['status'])) ?>"><?= e($agendamento['status']) ?></span>
              <div class="appointment-actions action-soft-group">
                <a class="action-soft-btn" href="editar-agendamento.php?id=<?= e($agendamento['id']) ?>" title="Editar">Editar</a>

                <?php if ($agendamento['status'] !== 'CONCLUIDO'): ?>
                  <form action="actions/alterar-status-agendamento.php" method="post">
                    <input type="hidden" name="agendamento_id" value="<?= e($agendamento['id']) ?>">
                    <input type="hidden" name="status" value="CONCLUIDO">
                    <button class="action-soft-btn" type="submit" title="Concluir">Concluir</button>
                  </form>
                <?php endif; ?>

                <form action="actions/alterar-status-agendamento.php" method="post">
                  <input type="hidden" name="agendamento_id" value="<?= e($agendamento['id']) ?>">
                  <input type="hidden" name="status" value="CANCELADO">
                  <button class="action-soft-btn" type="submit" title="Cancelar">Cancelar</button>
                </form>

                <form action="actions/excluir-agendamento.php" method="post" onsubmit="return confirm('Deseja excluir este agendamento?');">
                  <input type="hidden" name="agendamento_id" value="<?= e($agendamento['id']) ?>">
                  <button class="action-soft-btn" type="submit" title="Excluir">Excluir</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="admin-section" id="novo-agendamento">
        <div class="admin-section__header">
          <div>
            <span class="label">Agenda</span>
            <h2 class="admin-section__title">Cadastrar atendimento</h2>
          </div>
        </div>

        <form class="admin-form" action="actions/salvar-agendamentos.php" method="post">
          <label>
            Nome do cliente
            <input class="form-field" type="text" name="cliente_nome" placeholder="Digite o nome do cliente" required />
          </label>

          <label>
            Telefone do cliente
            <input class="form-field js-phone-mask" type="text" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}" name="cliente_telefone" placeholder="Ex: 19999999999" />
          </label>

          <label>
            E-mail do cliente
            <input class="form-field" type="email" name="cliente_email" placeholder="cliente@email.com" />
          </label>

          <label>
            Fotógrafo
            <select class="form-field" name="fotografo_id" required>
              <option value="">Selecione o fotógrafo</option>
              <?php foreach ($fotografos as $fotografo): ?>
                <option value="<?= e($fotografo['id']) ?>"><?= e($fotografo['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Serviço
            <select class="form-field" name="tipo_servico_id" required>
              <option value="">Selecione o serviço</option>
              <?php foreach ($tiposServico as $servico): ?>
                <option value="<?= e($servico['id']) ?>">
                  <?= e($servico['nicho']) ?> - <?= e($servico['nome']) ?> • <?= e($servico['duracao_minutos']) ?> min<?= $servico['exige_cenario'] ? ' • exige cenário' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Cenário
            <select class="form-field" name="cenario_id">
              <option value="">Sem cenário</option>
              <?php foreach ($cenarios as $cenario): ?>
                <option value="<?= e($cenario['id']) ?>">
                  <?= e($cenario['nicho']) ?> - <?= e($cenario['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Data
            <input class="form-field" type="date" name="data" required />
          </label>

          <label>
            Horário inicial
            <input class="form-field" type="time" name="hora_inicio" required />
          </label>

          <label>
            Valor
            <input class="form-field" type="number" name="valor" min="0" step="0.01" placeholder="Ex: 350.00" />
          </label>

          <label>
            Status
            <select class="form-field" name="status">
              <option value="CONFIRMADO">Confirmado</option>
              <option value="PENDENTE">Pendente</option>
            </select>
          </label>

          <label>
            Observação
            <textarea class="form-field" name="observacao" placeholder="Observações sobre o atendimento"></textarea>
          </label>

          <button class="btn btn--primary admin-form__submit form-submit-spaced" type="submit">Salvar atendimento</button>
        </form>
      </section>

      <section class="admin-section" id="pedidos-agendamento">
        <div class="admin-section__header">
          <div>
            <span class="label">Solicitações</span>
            <h2 class="admin-section__title">Pendentes para confirmar</h2>
          </div>
        </div>

        <div class="request-grid">
          <?php if (!$pendentes): ?>
            <p class="admin-empty">Nenhuma solicitação pendente no momento.</p>
          <?php endif; ?>

          <?php foreach ($pendentes as $agendamento): ?>
            <article class="request-card">
              <span class="status status--waiting">Pendente</span>
              <h3><?= e($agendamento['cliente']) ?></h3>
              <p>
                <?= e($agendamento['tipo_servico']) ?> em <?= e(formatDateBr($agendamento['data'])) ?>,
                das <?= e(formatPeriodoAgendamento($agendamento['data'], $agendamento['hora_inicio'], $agendamento['duracao_minutos'] ?? 60)) ?>.
              </p>
              <div class="request-card__meta">
                <span>Telefone: <?= e($agendamento['telefone_cliente']) ?></span>
                <span>E-mail: <?= e($agendamento['email_cliente'] ?? 'Não informado') ?></span>
                <span>Cenário: <?= e($agendamento['cenario'] ?: 'Sem cenário') ?></span>
              </div>
              <?php $whatsapp = whatsappLink($agendamento['telefone_cliente'] ?? null, $agendamento['cliente']); ?>

              <div class="request-card__actions request-actions-compact request-status-area">
                <form class="request-status-form" action="actions/alterar-status-agendamento.php" method="post">
                  <input type="hidden" name="agendamento_id" value="<?= e($agendamento['id']) ?>">

                  <select class="form-field form-field--small" name="status" required>
                    <option value="PENDENTE" selected>Manter pendente</option>
                    <option value="CONFIRMADO">Confirmar e enviar para agenda</option>
                    <option value="RECUSADO">Recusar e enviar para histórico</option>
                  </select>

                  <button class="mini-action mini-action--success" type="submit">Salvar status</button>
                </form>

                <?php if ($whatsapp): ?>
                  <a class="mini-action mini-action--whatsapp" href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Responder WhatsApp</a>
                <?php else: ?>
                  <span class="mini-action mini-action--disabled">Sem WhatsApp</span>
                <?php endif; ?>

                <?php if (!empty($agendamento['email_cliente']) && !str_contains($agendamento['email_cliente'], '@sememail.local')): ?>
                  <a class="mini-action mini-action--email" href="mailto:<?= e($agendamento['email_cliente']) ?>">Responder E-mail</a>
                <?php else: ?>
                  <span class="mini-action mini-action--disabled">Sem E-mail</span>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>


    </main>
    <a href="logout.php" class="btn btn--submit">Sair</a>
  </div>

  <script>
    function aplicarMascaraTelefoneDanny(valor) {
      const numeros = String(valor || '').replace(/\D/g, '').slice(0, 11);

      if (numeros.length <= 2) {
        return numeros;
      }

      if (numeros.length <= 7) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
      }

      return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7, 11)}`;
    }

    document.querySelectorAll('.js-phone-mask').forEach((input) => {
      input.addEventListener('input', () => {
        input.value = aplicarMascaraTelefoneDanny(input.value);
      });

      input.addEventListener('blur', () => {
        const numeros = input.value.replace(/\D/g, '');

        if (numeros && numeros.length !== 11) {
          input.setCustomValidity('Informe um telefone válido com DDD no formato (xx) xxxxx-xxxx.');
        } else {
          input.setCustomValidity('');
        }
      });

      input.value = aplicarMascaraTelefoneDanny(input.value);
    });
  </script>

</body>
</html>
