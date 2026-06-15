<?php
session_start();

require_once __DIR__ . '/src/bootstrap.php';

use Danny\Database;

if (empty($_SESSION['admin_autorizado'])) {
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
$historico = [];
  $totalValoresHistorico = 0.0;
$filtros = [];
$totalValoresHistorico = 0.0;

$busca = trim($_GET['busca'] ?? '');
$filtro = trim($_GET['filtro'] ?? '');

try {
  $pdo = Database::connection();

  $telefoneClienteSql = colunaExiste($pdo, 'admins', 'telefone')
    ? "u.telefone AS telefone_cliente,"
    : "NULL AS telefone_cliente,";

  $filtros = $pdo->query("
    SELECT DISTINCT nome FROM (
      SELECT n.nome
      FROM nichos n
      INNER JOIN tipos_servico ts ON ts.nicho_id = n.id

      UNION

      SELECT ts.nome
      FROM tipos_servico ts
    ) filtros
    WHERE nome IS NOT NULL AND nome <> ''
    ORDER BY nome
  ")->fetchAll();

  $sqlHistorico = "
    SELECT
      a.id,
      u.nome AS cliente,
      u.email AS email_cliente,
      {$telefoneClienteSql}
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
    WHERE a.status IN ('CONCLUIDO', 'RECUSADO', 'CANCELADO')
  ";

  $params = [];

  if ($busca !== '') {
    $sqlHistorico .= "
      AND (
        u.nome LIKE :busca_cliente
        OR f.nome LIKE :busca_fotografo
        OR ts.nome LIKE :busca_servico
        OR n.nome LIKE :busca_nicho
        OR c.nome LIKE :busca_cenario
        OR a.observacao LIKE :busca_observacao
        OR a.status LIKE :busca_status
      )
    ";

    $termoBusca = '%' . $busca . '%';

    $params[':busca_cliente'] = $termoBusca;
    $params[':busca_fotografo'] = $termoBusca;
    $params[':busca_servico'] = $termoBusca;
    $params[':busca_nicho'] = $termoBusca;
    $params[':busca_cenario'] = $termoBusca;
    $params[':busca_observacao'] = $termoBusca;
    $params[':busca_status'] = $termoBusca;
  }

  if ($filtro !== '') {
    $sqlHistorico .= "
      AND (
        n.nome = :filtro_nicho
        OR ts.nome = :filtro_servico
      )
    ";

    $params[':filtro_nicho'] = $filtro;
    $params[':filtro_servico'] = $filtro;
  }

  $sqlHistorico .= "
    ORDER BY a.data DESC, a.hora_inicio DESC
  ";

  $stmt = $pdo->prepare($sqlHistorico);
  $stmt->execute($params);
  $historico = $stmt->fetchAll();

  foreach ($historico as $itemHistorico) {
    $totalValoresHistorico += (float) ($itemHistorico['valor'] ?? 0);
  }
} catch (PDOException $e) {
  $erroBanco = null;
  $historico = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Histórico - Danny</title>
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
          <span class="label">Histórico</span>
          <h1 class="admin-title">Atendimentos encerrados.</h1>
        </div>
        <a class="btn btn--outline" href="admin.php#agenda">Voltar para agenda</a>
      </header>

      <section class="admin-section">
        <div class="admin-section__header">
          <div>
            <span class="label">Filtros</span>
            <h2 class="admin-section__title">Buscar no histórico</h2>
          </div>
        </div>

        <form class="history-filter-form" action="historico.php" method="get">
          <label>
            Buscar
            <input
              class="form-field"
              type="search"
              name="busca"
              value="<?= e($busca) ?>"
              placeholder="Cliente, fotógrafo, cenário, serviço..."
            />
          </label>

          <label>
            Filtrar por tipo/nicho
            <select class="form-field" name="filtro">
              <option value="">Todos</option>
              <?php foreach ($filtros as $item): ?>
                <option value="<?= e($item['nome']) ?>" <?= $filtro === $item['nome'] ? 'selected' : '' ?>>
                  <?= e($item['nome']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <div class="history-filter-actions">
            <button class="action-soft-btn" type="submit">Buscar</button>
            <a class="action-soft-btn" href="historico.php">Limpar</a>
          </div>
        </form>
      </section>

      <section class="admin-section">
        <div class="admin-section__header">
          <div>
            <span class="label">Registros</span>
            <h2 class="admin-section__title">Histórico de agendamentos</h2>
          </div>
          <div class="history-summary">
            <strong class="admin-total"><?= e(count($historico)) ?> registro(s)</strong>
            <strong class="admin-total">Total em valores: <?= e(formatMoneyBr($totalValoresHistorico)) ?></strong>
          </div>
        </div>

        <div class="schedule-list">
          <?php if (!$historico): ?>
            <div class="history-empty-card">
              <div class="history-empty-card__icon">!</div>
              <div>
                <h3>Nenhum atendimento encontrado</h3>

                <?php if ($busca !== '' || $filtro !== ''): ?>
                  <p>
                    Não encontrei registros para
                    <?php if ($busca !== ''): ?>
                      <strong>"<?= e($busca) ?>"</strong>
                    <?php endif; ?>

                    <?php if ($busca !== '' && $filtro !== ''): ?>
                      com o filtro
                    <?php elseif ($filtro !== ''): ?>
                      o filtro
                    <?php endif; ?>

                    <?php if ($filtro !== ''): ?>
                      <strong>"<?= e($filtro) ?>"</strong>
                    <?php endif; ?>.
                  </p>
                  <p class="history-empty-card__hint">
                    Tente buscar por outro cliente, serviço, nicho ou limpe os filtros para ver todos os atendimentos encerrados.
                  </p>
                  <a class="action-soft-btn" href="historico.php">Limpar filtros</a>
                <?php else: ?>
                  <p>Ainda não existe nenhum atendimento concluído, recusado ou cancelado no histórico.</p>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php foreach ($historico as $agendamento): ?>
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
                <?php if (!empty($agendamento['telefone_cliente']) && $agendamento['telefone_cliente'] !== 'Não informado'): ?>
                  <p>Telefone: <?= e($agendamento['telefone_cliente']) ?></p>
                <?php endif; ?>
                <?php if (!empty($agendamento['email_cliente']) && !str_contains($agendamento['email_cliente'], '@sememail.local')): ?>
                  <p>E-mail: <?= e($agendamento['email_cliente']) ?></p>
                <?php endif; ?>
                <?php if (!empty($agendamento['observacao'])): ?>
                  <p>Obs.: <?= e($agendamento['observacao']) ?></p>
                <?php endif; ?>
              </div>
              <span class="status <?= e(statusClass($agendamento['status'])) ?>"><?= e($agendamento['status']) ?></span>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
