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

function mensagemErro(string $erro): string
{
  return match ($erro) {
    'campos' => 'Preencha todos os campos obrigatórios.',
    'banco' => 'Erro ao atualizar o agendamento.',
    'antecedencia' => 'Escolha uma data com pelo menos 7 dias de antecedência.',
    'horario_limite' => 'Não foi possível salvar o atendimento. O horário inicial deve estar dentro do período permitido de atendimento, entre 06:00 e 20:00.',
    'servico' => 'Tipo de serviço inválido.',
    'cenario_obrigatorio' => 'Este serviço exige cenário.',
    'cenario' => 'Cenário inválido.',
    'cenario_nicho' => 'O cenário não pertence ao mesmo nicho do serviço.',
    'cenario_mes' => 'O cenário não está disponível para o mês escolhido.',
    'cenario_ano' => 'O cenário não está disponível para o ano escolhido.',
    'disponibilidade' => 'O fotógrafo não possui disponibilidade para esse período.',
    'conflito' => 'Já existe um agendamento nesse período para o fotógrafo escolhido.',
    default => 'Ocorreu um erro. Verifique os dados.',
  };
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
  header('Location: admin.php?erro=status#agenda');
  exit;
}

$erroBanco = null;
$agendamento = null;
$fotografos = [];
$tiposServico = [];
$cenarios = [];

try {
  $pdo = Database::connection();

  $stmt = $pdo->prepare("
    SELECT
      a.*,
      u.nome AS cliente_nome
    FROM agendamentos a
    INNER JOIN admins u ON u.id = a.admin_id
    WHERE a.id = :id
    LIMIT 1
  ");
  $stmt->execute([':id' => $id]);
  $agendamento = $stmt->fetch();

  if (!$agendamento) {
    header('Location: admin.php?erro=status#agenda');
    exit;
  }

  $fotografos = $pdo->query("SELECT id, nome FROM fotografos ORDER BY nome")->fetchAll();

  $tiposServico = $pdo->query("
    SELECT ts.id, ts.nome, ts.duracao_minutos, ts.exige_cenario, COALESCE(n.nome, 'Sem nicho') AS nicho
    FROM tipos_servico ts
    LEFT JOIN nichos n ON n.id = ts.nicho_id
    ORDER BY n.nome, ts.nome
  ")->fetchAll();

  $cenarios = $pdo->query("
    SELECT c.id, c.nome, c.mes, c.ano, COALESCE(n.nome, 'Sem nicho') AS nicho
    FROM cenarios c
    LEFT JOIN nichos n ON n.id = c.nicho_id
    ORDER BY n.nome, c.nome
  ")->fetchAll();
} catch (PDOException $e) {
  $erroBanco = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar agendamento - Danny</title>
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
          <span class="label">Agenda</span>
          <h1 class="admin-title">Editar agendamento.</h1>
        </div>
        <a class="btn btn--outline" href="admin.php#agenda">Voltar</a>
      </header>

      <?php if ($erroBanco): ?>
        <section class="admin-section">
          <p style="color: red; font-weight: bold;">Erro ao buscar dados do banco: <?= e($erroBanco) ?></p>
        </section>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <p style="color: red; font-weight: bold;"><?= e(mensagemErro((string) $_GET['erro'])) ?></p>
      <?php endif; ?>

      <?php if ($agendamento): ?>
        <section class="admin-section">
          <form class="admin-form" action="actions/atualizar-agendamento.php" method="post">
            <input type="hidden" name="agendamento_id" value="<?= e($agendamento['id']) ?>">

            <label>
              Nome do cliente
              <input class="form-field" type="text" name="cliente_nome" value="<?= e($agendamento['cliente_nome']) ?>" required />
            </label>

            <label>
              Fotógrafo
              <select class="form-field" name="fotografo_id" required>
                <?php foreach ($fotografos as $fotografo): ?>
                  <option value="<?= e($fotografo['id']) ?>" <?= (int) $fotografo['id'] === (int) $agendamento['fotografo_id'] ? 'selected' : '' ?>>
                    <?= e($fotografo['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label>
              Serviço
              <select class="form-field" name="tipo_servico_id" required>
                <?php foreach ($tiposServico as $servico): ?>
                  <option value="<?= e($servico['id']) ?>" <?= (int) $servico['id'] === (int) $agendamento['tipo_servico_id'] ? 'selected' : '' ?>>
                    <?= e($servico['nicho']) ?> - <?= e($servico['nome']) ?> • <?= e($servico['duracao_minutos']) ?> min
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label>
              Cenário
              <select class="form-field" name="cenario_id">
                <option value="">Sem cenário</option>
                <?php foreach ($cenarios as $cenario): ?>
                  <option value="<?= e($cenario['id']) ?>" <?= (int) $cenario['id'] === (int) ($agendamento['cenario_id'] ?? 0) ? 'selected' : '' ?>>
                    <?= e($cenario['nicho']) ?> - <?= e($cenario['nome']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label>
              Data
              <input class="form-field" type="date" name="data" value="<?= e($agendamento['data']) ?>" required />
            </label>

            <label>
              Horário inicial
              <input class="form-field" type="time" name="hora_inicio" value="<?= e(substr((string) $agendamento['hora_inicio'], 0, 5)) ?>" required />
            </label>

            <label>
              Valor
              <input class="form-field" type="number" name="valor" min="0" step="0.01" value="<?= e($agendamento['valor'] ?? '') ?>" placeholder="Ex: 350.00" />
            </label>

            <label>
              Status
              <select class="form-field" name="status" required>
                <?php foreach (['PENDENTE', 'CONFIRMADO', 'RECUSADO', 'CANCELADO', 'CONCLUIDO'] as $status): ?>
                  <option value="<?= e($status) ?>" <?= $status === $agendamento['status'] ? 'selected' : '' ?>>
                    <?= e($status) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </label>

            <label>
              Observação
              <textarea class="form-field" name="observacao"><?= e($agendamento['observacao'] ?? '') ?></textarea>
            </label>

            <button class="btn btn--primary admin-form__submit" type="submit">Salvar alterações</button>
          </form>
        </section>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>
