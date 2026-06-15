<?php
session_start();

require_once __DIR__ . '/src/bootstrap.php';

use Danny\Database;

if (!isset($_SESSION['cliente_id'])) {
  header('Location: login-cliente.php');
  exit;
}

function e(mixed $value): string
{
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatDateBr(?string $date): string
{
  if (!$date) {
    return '-';
  }

  return date('d/m/Y', strtotime($date));
}

function formatTimeBr(?string $time): string
{
  if (!$time) {
    return '-';
  }

  return substr($time, 0, 5);
}

function formatDuracaoBr(mixed $minutos): string
{
  $minutos = (int) ($minutos ?: 60);

  if ($minutos >= 1440 && $minutos % 1440 === 0) {
    $dias = intdiv($minutos, 1440);
    return $dias === 1 ? '24 horas' : $dias . ' dias';
  }

  if ($minutos >= 60) {
    $horas = intdiv($minutos, 60);
    $resto = $minutos % 60;

    if ($resto === 0) {
      return $horas === 1 ? '1 hora' : $horas . ' horas';
    }

    return $horas . 'h ' . $resto . 'min';
  }

  return $minutos . ' min';
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

$erroBanco = null;
$cliente = null;
$nichos = [];
$tiposServico = [];
$cenarios = [];
$fotografos = [];
$agendamentos = [];

try {
  $pdo = Database::connection();

  $stmt = $pdo->prepare("
    SELECT u.*, n.nome AS nicho_nome, ts.nome AS tipo_servico_nome
    FROM admins u
    LEFT JOIN nichos n ON n.id = u.nicho_id
    LEFT JOIN tipos_servico ts ON ts.id = u.tipo_servico_id
    WHERE u.id = :id
      AND u.tipo = 'CLIENTE'
    LIMIT 1
  ");
  $stmt->execute([':id' => $_SESSION['cliente_id']]);
  $cliente = $stmt->fetch();

  $nichos = $pdo->query("
    SELECT id, nome
    FROM nichos
    ORDER BY nome
  ")->fetchAll();

  $tiposServico = $pdo->query("
    SELECT ts.id, ts.nome, ts.nicho_id, ts.duracao_minutos, ts.exige_cenario, n.nome AS nicho
    FROM tipos_servico ts
    INNER JOIN nichos n ON n.id = ts.nicho_id
    ORDER BY n.nome, ts.nome
  ")->fetchAll();

  $cenarios = $pdo->query("
    SELECT c.id, c.nome, c.nicho_id, c.mes, c.ano, COALESCE(n.nome, 'Sem nicho') AS nicho
    FROM cenarios c
    LEFT JOIN nichos n ON n.id = c.nicho_id
    ORDER BY n.nome, c.nome
  ")->fetchAll();

  $fotografos = $pdo->query("
    SELECT id, nome
    FROM fotografos
    ORDER BY nome
  ")->fetchAll();

  $stmtAgendamentos = $pdo->prepare("
    SELECT
      a.id,
      u.nome AS cliente,
      f.nome AS fotografo,
      COALESCE(n.nome, 'Não informado') AS nicho,
      ts.nome AS tipo_servico,
      COALESCE(ts.duracao_minutos, 60) AS duracao_minutos,
      c.nome AS cenario,
      a.data,
      a.hora_inicio,
      a.observacao,
      a.status
    FROM agendamentos a
    INNER JOIN admins u ON u.id = a.admin_id
    INNER JOIN fotografos f ON f.id = a.fotografo_id
    INNER JOIN tipos_servico ts ON ts.id = a.tipo_servico_id
    LEFT JOIN nichos n ON n.id = ts.nicho_id
    LEFT JOIN cenarios c ON c.id = a.cenario_id
    WHERE a.admin_id = :cliente_id
    ORDER BY a.data DESC, a.hora_inicio DESC
  ");
  $stmtAgendamentos->execute([':cliente_id' => $_SESSION['cliente_id']]);
  $agendamentos = $stmtAgendamentos->fetchAll();
} catch (PDOException $e) {
  $erroBanco = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Área do Cliente — Danny</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="admin-page">
  <div class="admin-shell">
    <aside class="admin-sidebar">
      <a class="admin-sidebar__brand" href="index.php">Danny</a>
      <nav class="admin-sidebar__nav">
        <a href="#solicitar">Solicitar agendamento</a>
        <a href="#meus-agendamentos">Meus agendamentos</a>
        <a href="logout-cliente.php">Sair</a>
      </nav>
    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <div>
          <span class="label">Área do cliente</span>
          <h1 class="admin-title">Olá, <?= e($_SESSION['cliente_nome']) ?>.</h1>
        </div>
        <a class="btn btn--primary" href="#solicitar">Solicitar horário</a>
      </header>

      <?php if ($erroBanco): ?>
        <section class="admin-section">
          <p style="color: red; font-weight: bold;">Erro ao buscar dados do banco: <?= e($erroBanco) ?></p>
        </section>
      <?php endif; ?>

      <?php if (isset($_GET['agendamento']) && $_GET['agendamento'] === 'sucesso'): ?>
        <div class="client-feedback client-feedback--success">Solicitação enviada com sucesso. Agora ela ficará pendente até o administrador confirmar.</div>
      <?php endif; ?>

      <?php if (isset($_GET['perfil']) && $_GET['perfil'] === 'atualizado'): ?>
        <div class="client-feedback client-feedback--success">Perfil atualizado com sucesso.</div>
      <?php endif; ?>

      <?php if (isset($_GET['cancelamento']) && $_GET['cancelamento'] === 'sucesso'): ?>
        <div class="client-feedback client-feedback--success">Solicitação cancelada com sucesso.</div>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <div class="client-feedback client-feedback--error">
          <?php
            echo match ($_GET['erro']) {
              'campos' => 'Preencha todos os campos obrigatórios antes de enviar a solicitação.',
              'servico' => 'O serviço selecionado não pertence ao interesse escolhido. Verifique o campo Seu interesse e Serviço inicial.',
              'horario_limite' => 'Não foi possível enviar a solicitação. O horário inicial deve estar dentro do período permitido, entre 06:00 e 20:00.',
              'antecedencia' => 'Escolha uma data com pelo menos 7 dias de antecedência.',
              'cenario_obrigatorio' => 'Este serviço exige um cenário. Selecione um cenário antes de enviar.',
              'cenario_nicho' => 'O cenário escolhido não pertence ao mesmo interesse do serviço selecionado.',
              'cenario_mes' => 'O cenário escolhido não está disponível para o mês selecionado.',
              'cenario_ano' => 'O cenário escolhido não está disponível para o ano selecionado.',
              'conflito' => 'Não foi possível enviar a solicitação porque o horário escolhido entra em conflito com outro atendimento.',
              'cancelamento' => 'Não foi possível cancelar esta solicitação. Apenas solicitações pendentes podem ser canceladas pelo cliente.',
              default => 'Ocorreu um erro ao enviar a solicitação. Verifique os dados informados.',
            };
          ?>
        </div>
      <?php endif; ?>

      <section class="admin-section" id="perfil-cliente">
        <div class="admin-section__header">
          <div>
            <span class="label">Perfil</span>
            <h2 class="admin-section__title">Meus dados e preferências</h2>
          </div>
        </div>

        <form class="admin-form cliente-profile-form" action="actions/atualizar-perfil-cliente.php" method="post">
          <label>
            Nome
            <input class="form-field" type="text" name="cliente_nome" value="<?= e($cliente['nome'] ?? $_SESSION['cliente_nome']) ?>" required />
          </label>

          <label>
            Telefone
            <input class="form-field js-phone-mask" type="text" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}" name="cliente_telefone" value="<?= e($cliente['telefone'] ?? '') ?>" placeholder="Ex: 19999999999" />
          </label>

          <label>
            E-mail
            <input class="form-field" type="email" name="cliente_email" value="<?= e($cliente['email'] ?? '') ?>" placeholder="cliente@email.com" />
          </label>

          <label>
            Seu interesse
            <select class="form-field" name="nicho_id" id="perfil_nicho_id" required>
              <option value="">Selecione</option>
              <?php foreach ($nichos as $nicho): ?>
                <option value="<?= e($nicho['id']) ?>" <?= $cliente && (string) ($cliente['nicho_id'] ?? '') === (string) $nicho['id'] ? 'selected' : '' ?>>
                  <?= e($nicho['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Serviço inicial
            <select class="form-field" name="tipo_servico_id" id="perfil_tipo_servico_id" required>
              <option value="">Selecione</option>
              <?php foreach ($tiposServico as $servico): ?>
                <option value="<?= e($servico['id']) ?>" data-nicho="<?= e($servico['nicho_id']) ?>" <?= $cliente && (string) ($cliente['tipo_servico_id'] ?? '') === (string) $servico['id'] ? 'selected' : '' ?>>
                  <?= e($servico['nicho']) ?> - <?= e($servico['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <button class="btn btn--submit" type="submit">Salvar perfil</button>
        </form>
      </section>

      <section class="admin-section" id="solicitar">
        <div class="admin-section__header">
          <div>
            <span class="label">Novo pedido</span>
            <h2 class="admin-section__title">Solicitar agendamento</h2>
          </div>
        </div>

        <p class="admin-empty" style="margin-bottom: 1rem;">
          Escolha o serviço, data e horário inicial. O pedido será enviado como pendente e ficará aguardando a confirmação do administrador.
        </p>

        <form class="admin-form cliente-request-form" action="actions/salvar-agendamento-cliente.php" method="post">
          <label>
            Serviço
            <select class="form-field" name="tipo_servico_id" id="tipo_servico_id" required>
              <option value="">Selecione</option>
              <?php foreach ($tiposServico as $servico): ?>
                <option value="<?= e($servico['id']) ?>" data-nicho="<?= e($servico['nicho_id']) ?>" data-exige-cenario="<?= e($servico['exige_cenario']) ?>" <?= $cliente && (string) ($cliente['tipo_servico_id'] ?? '') === (string) $servico['id'] ? 'selected' : '' ?>>
                  <?= e($servico['nicho']) ?> - <?= e($servico['nome']) ?> — <?= e($servico['duracao_minutos']) ?> min
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Fotógrafo
            <select class="form-field" name="fotografo_id" required>
              <option value="">Selecione</option>
              <?php foreach ($fotografos as $fotografo): ?>
                <option value="<?= e($fotografo['id']) ?>"><?= e($fotografo['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Cenário
            <select class="form-field" name="cenario_id" id="cenario_id">
              <option value="">Sem cenário</option>
              <?php foreach ($cenarios as $cenario): ?>
                <option value="<?= e($cenario['id']) ?>" data-nicho="<?= e($cenario['nicho_id']) ?>">
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
            Hora inicial
            <input class="form-field" type="time" name="hora_inicio" required />
          </label>

          <label style="grid-column: 1 / -1;">
            Observação
            <textarea class="form-field form-field--textarea" name="observacao" placeholder="Escreva alguma observação, se necessário"></textarea>
          </label>

          <input type="hidden" name="status" value="PENDENTE" />
          <button class="btn btn--submit" type="submit">Enviar solicitação pendente</button>
        </form>
      </section>

      <section class="admin-section" id="meus-agendamentos">
        <div class="admin-section__header">
          <div>
            <span class="label">Acompanhamento</span>
            <h2 class="admin-section__title">Minhas solicitações e agendamentos</h2>
          </div>
        </div>

        <div class="schedule-list">
          <?php if (!$agendamentos): ?>
            <p class="admin-empty">Você ainda não possui agendamentos.</p>
          <?php endif; ?>

          <?php foreach ($agendamentos as $agendamento): ?>
            <article class="schedule-item">
              <time class="schedule-item__time" datetime="<?= e($agendamento['data'] . 'T' . $agendamento['hora_inicio']) ?>">
                <span><?= e(formatDateBr($agendamento['data'])) ?></span>
                <?= e(formatPeriodoAgendamento($agendamento['data'], $agendamento['hora_inicio'], $agendamento['duracao_minutos'])) ?>
              </time>

              <div>
                <h3><?= e($agendamento['tipo_servico']) ?></h3>
                <p>
                  <?= e($agendamento['nicho']) ?> •
                  Fotógrafo: <?= e($agendamento['fotografo']) ?> •
                  Cenário: <?= e($agendamento['cenario'] ?: 'Sem cenário') ?>
                </p>
                <p>Duração: <?= e(formatDuracaoBr($agendamento['duracao_minutos'])) ?></p>
              </div>

              <span class="status <?= e(statusClass($agendamento['status'])) ?>"><?= e($agendamento['status']) ?></span>

              <?php if ($agendamento['status'] === 'PENDENTE'): ?>
                <form class="client-cancel-form" action="actions/cancelar-agendamento-cliente.php" method="post">
                  <input type="hidden" name="agendamento_id" value="<?= e($agendamento['id']) ?>">
                  <button
                    class="mini-action mini-action--danger"
                    type="submit"
                    onclick="return confirm('Deseja cancelar esta solicitação pendente?');"
                  >
                    Cancelar solicitação
                  </button>
                </form>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </main>
  </div>

  <script>
    const perfilNichoSelect = document.getElementById('perfil_nicho_id');
    const perfilServicoSelect = document.getElementById('perfil_tipo_servico_id');
    const pedidoServicoSelect = document.getElementById('tipo_servico_id');
    const cenarioSelect = document.getElementById('cenario_id');

    const perfilServicoOptions = perfilServicoSelect ? Array.from(perfilServicoSelect.options) : [];
    const pedidoServicoOptions = pedidoServicoSelect ? Array.from(pedidoServicoSelect.options) : [];
    const cenarioOptions = cenarioSelect ? Array.from(cenarioSelect.options) : [];

    function filtrarPerfil() {
      if (!perfilNichoSelect || !perfilServicoSelect) {
        return;
      }

      const nichoId = perfilNichoSelect.value;

      perfilServicoOptions.forEach((option) => {
        if (!option.value) {
          option.hidden = false;
          return;
        }

        option.hidden = nichoId && option.dataset.nicho !== nichoId;
      });

      const selecionado = perfilServicoSelect.options[perfilServicoSelect.selectedIndex];

      if (selecionado && selecionado.hidden) {
        perfilServicoSelect.value = '';
      }
    }

    function filtrarPedido() {
      if (!pedidoServicoSelect || !cenarioSelect) {
        return;
      }

      const servicoSelecionado = pedidoServicoSelect.options[pedidoServicoSelect.selectedIndex];
      const nichoId = servicoSelecionado ? servicoSelecionado.dataset.nicho : '';

      cenarioOptions.forEach((option) => {
        if (!option.value) {
          option.hidden = false;
          return;
        }

        option.hidden = nichoId && option.dataset.nicho !== nichoId;
      });

      const cenarioSelecionado = cenarioSelect.options[cenarioSelect.selectedIndex];

      if (cenarioSelecionado && cenarioSelecionado.hidden) {
        cenarioSelect.value = '';
      }
    }

    perfilNichoSelect?.addEventListener('change', filtrarPerfil);
    pedidoServicoSelect?.addEventListener('change', filtrarPedido);

    filtrarPerfil();
    filtrarPedido();
  </script>

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
