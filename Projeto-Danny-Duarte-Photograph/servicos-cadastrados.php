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


function garantirColuna(PDO $pdo, string $tabela, string $coluna, string $definicao): void
{
  if (!colunaExiste($pdo, $tabela, $coluna)) {
    $pdo->exec("ALTER TABLE `{$tabela}` ADD COLUMN `{$coluna}` {$definicao}");
  }
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

$erroBanco = null;
$nichos = [];
$fotografos = [];
$tiposServico = [];
$cenarios = [];

try {
  $pdo = Database::connection();

  $nichos = $pdo->query("SELECT id, nome FROM nichos ORDER BY nome")->fetchAll();

  garantirColuna($pdo, 'fotografos', 'telefone', "VARCHAR(20) DEFAULT NULL");
  garantirColuna($pdo, 'fotografos', 'email', "VARCHAR(150) DEFAULT NULL");

  $telefoneFotografoSql = "telefone";
  $emailFotografoSql = "email";

  $fotografos = $pdo->query("
    SELECT id, nome, {$telefoneFotografoSql}, {$emailFotografoSql}
    FROM fotografos
    ORDER BY nome
  ")->fetchAll();

  $tiposServico = $pdo->query("
    SELECT
      ts.id,
      ts.nome,
      ts.duracao_minutos,
      ts.exige_cenario,
      ts.nicho_id,
      COALESCE(n.nome, 'Sem nicho') AS nicho
    FROM tipos_servico ts
    LEFT JOIN nichos n ON n.id = ts.nicho_id
    ORDER BY n.nome, ts.nome
  ")->fetchAll();

  $cenarios = $pdo->query("
    SELECT
      c.id,
      c.nome,
      c.nicho_id,
      c.mes,
      c.ano,
      COALESCE(n.nome, 'Sem nicho') AS nicho
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
  <title>Serviços cadastrados - Danny</title>
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
          <span class="label">Banco de dados</span>
          <h1 class="admin-title">Serviços cadastrados.</h1>
        </div>
        <a class="btn btn--outline" href="admin.php#agenda">Voltar para agenda</a>
      </header>

      <?php if ($erroBanco): ?>
        <section class="admin-section">
          <p style="color: red; font-weight: bold;">Erro ao buscar dados do banco: <?= e($erroBanco) ?></p>
        </section>
      <?php endif; ?>

      <?php if (isset($_GET['opcao']) && $_GET['opcao'] === 'sucesso'): ?>
        <p style="color: green; font-weight: bold;">Opção salva com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['opcao']) && $_GET['opcao'] === 'excluida'): ?>
        <p style="color: green; font-weight: bold;">Opção excluída com sucesso.</p>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <p style="color: red; font-weight: bold;">Erro ao executar ação. Se foi ao salvar pendente, rode o SQL de correção das triggers no phpMyAdmin.</p>
      <?php endif; ?>

      <section class="admin-section">
        <div class="admin-section__header">
          <div>
            <span class="label">Cadastrar</span>
            <h2 class="admin-section__title">Adicionar opções</h2>
          </div>
        </div>

        <div class="options-grid options-grid--three services-register-grid">
          <article class="option-card">
            <h3>Adicionar serviço</h3>
            <form class="option-form option-form--inline" action="actions/salvar-servico.php" method="post">
              <div class="form-inline-row">
                <input class="form-field" type="text" name="nome" placeholder="Nome do serviço" required>
                <select class="form-field" name="nicho_id" required>
                  <option value="">Nicho</option>
                  <?php foreach ($nichos as $nicho): ?>
                    <option value="<?= e($nicho['id']) ?>"><?= e($nicho['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-inline-row">
                <input class="form-field" type="number" name="duracao_minutos" placeholder="Duração em minutos" min="1" value="60" required>
                <label class="check-row check-row--card">
                  <input type="checkbox" name="exige_cenario" value="1">
                  Exige cenário
                </label>
              </div>

              <button class="btn btn--primary option-submit" type="submit">Salvar serviço</button>
            </form>
          </article>

          <article class="option-card">
            <h3>Adicionar cenário</h3>
            <form class="option-form option-form--inline" action="actions/salvar-cenario.php" method="post">
              <div class="form-inline-row">
                <input class="form-field" type="text" name="nome" placeholder="Nome do cenário" required>
                <select class="form-field" name="nicho_id" required>
                  <option value="">Nicho</option>
                  <?php foreach ($nichos as $nicho): ?>
                    <option value="<?= e($nicho['id']) ?>"><?= e($nicho['nome']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-inline-row">
                <input class="form-field" type="number" name="mes" placeholder="Mês, se for sazonal" min="1" max="12">
                <input class="form-field" type="number" name="ano" placeholder="Ano, se tiver">
              </div>

              <button class="btn btn--primary option-submit" type="submit">Salvar cenário</button>
            </form>
          </article>

          <article class="option-card">
            <h3>Adicionar fotógrafo</h3>
            <form class="option-form option-form--inline" action="actions/salvar-fotografo.php" method="post">
              <div class="form-inline-row">
                <input class="form-field" type="text" name="nome" placeholder="Nome do fotógrafo" required>
                <input class="form-field js-phone-mask" type="text" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}" name="telefone" placeholder="Telefone">
              </div>

              <input class="form-field" type="email" name="email" placeholder="E-mail">
              <button class="btn btn--primary option-submit" type="submit">Salvar fotógrafo</button>
            </form>
          </article>
        </div>
      </section>

      <section class="admin-section" id="servicos-lista">
        <div class="admin-section__header">
          <div>
            <span class="label">Serviços</span>
            <h2 class="admin-section__title">Serviços cadastrados</h2>
          </div>
        </div>

        <div class="admin-table options-table service-table" role="table" aria-label="Tipos de serviço">
          <div class="admin-table__row admin-table__row--head option-row--five" role="row">
            <span>Nicho</span>
            <span>Serviço</span>
            <span>Duração</span>
            <span>Cenário</span>
            <span>Ações</span>
          </div>

          <div class="admin-table__body">
            <?php if (!$tiposServico): ?>
              <p class="admin-empty">Nenhum serviço cadastrado.</p>
            <?php endif; ?>

            <?php foreach ($tiposServico as $servico): ?>
              <form class="admin-table__row option-row option-row--five service-edit-row" action="actions/salvar-servico.php" method="post" role="row">
                <input type="hidden" name="id" value="<?= e($servico['id']) ?>">
                <input type="hidden" name="tipo" value="servico">

                <span>
                  <select class="form-field form-field--small" name="nicho_id" required>
                    <?php foreach ($nichos as $nicho): ?>
                      <option value="<?= e($nicho['id']) ?>" <?= (int) $nicho['id'] === (int) $servico['nicho_id'] ? 'selected' : '' ?>>
                        <?= e($nicho['nome']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </span>

                <span>
                  <input class="form-field form-field--small" type="text" name="nome" value="<?= e($servico['nome']) ?>" placeholder="Nome do serviço" required>
                </span>

                <span>
                  <input class="form-field form-field--small" type="number" name="duracao_minutos" value="<?= e($servico['duracao_minutos']) ?>" min="1" required>
                </span>

                <span>
                  <label class="check-row check-row--table">
                    <input type="checkbox" name="exige_cenario" value="1" <?= $servico['exige_cenario'] ? 'checked' : '' ?>>
                    Exige
                  </label>
                </span>

                <span class="option-actions service-actions">
                  <button class="action-soft-btn" type="submit">Salvar</button>
                  <button class="action-soft-btn" type="submit" formaction="actions/excluir-opcao.php" formmethod="post" name="tipo" value="servico" onclick="return confirm('Excluir este serviço?');">Excluir</button>
                </span>
              </form>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="admin-section" id="cenarios-lista">
        <div class="admin-section__header">
          <div>
            <span class="label">Cenários</span>
            <h2 class="admin-section__title">Cenários cadastrados</h2>
          </div>
        </div>

        <div class="admin-table options-table scenario-table" role="table" aria-label="Cenários">
          <div class="admin-table__row admin-table__row--head option-row--five" role="row">
            <span>Nicho</span>
            <span>Cenário</span>
            <span>Mês</span>
            <span>Ano</span>
            <span>Ações</span>
          </div>

          <div class="admin-table__body">
            <?php if (!$cenarios): ?>
              <p class="admin-empty">Nenhum cenário cadastrado.</p>
            <?php endif; ?>

            <?php foreach ($cenarios as $cenario): ?>
              <form class="admin-table__row option-row option-row--five scenario-edit-row" action="actions/salvar-cenario.php" method="post" role="row">
                <input type="hidden" name="id" value="<?= e($cenario['id']) ?>">
                <input type="hidden" name="tipo" value="cenario">

                <span>
                  <select class="form-field form-field--small" name="nicho_id" required>
                    <?php foreach ($nichos as $nicho): ?>
                      <option value="<?= e($nicho['id']) ?>" <?= (int) $nicho['id'] === (int) $cenario['nicho_id'] ? 'selected' : '' ?>>
                        <?= e($nicho['nome']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </span>

                <span>
                  <input class="form-field form-field--small" type="text" name="nome" value="<?= e($cenario['nome']) ?>" placeholder="Nome do cenário" required>
                </span>

                <span>
                  <input class="form-field form-field--small" type="number" name="mes" value="<?= e($cenario['mes'] ?? '') ?>" min="1" max="12" placeholder="-">
                </span>

                <span>
                  <input class="form-field form-field--small" type="number" name="ano" value="<?= e($cenario['ano'] ?? '') ?>" placeholder="Todo ano">
                </span>

                <span class="option-actions scenario-actions">
                  <button class="action-soft-btn" type="submit">Salvar</button>
                  <button class="action-soft-btn" type="submit" formaction="actions/excluir-opcao.php" formmethod="post" name="tipo" value="cenario" onclick="return confirm('Excluir este cenário?');">Excluir</button>
                </span>
              </form>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="admin-section" id="fotografos-lista">
        <div class="admin-section__header">
          <div>
            <span class="label">Fotógrafos</span>
            <h2 class="admin-section__title">Fotógrafos cadastrados</h2>
          </div>
        </div>

        <div class="admin-table options-table photographer-table" role="table" aria-label="Fotógrafos">
          <div class="admin-table__row admin-table__row--head option-row--four" role="row">
            <span>Fotógrafo</span><span>Telefone</span><span>E-mail</span><span>Ações</span>
          </div>

          <div class="admin-table__body">
            <?php if (!$fotografos): ?>
              <p class="admin-empty">Nenhum fotógrafo cadastrado.</p>
            <?php endif; ?>

            <?php foreach ($fotografos as $fotografo): ?>
              <form class="admin-table__row option-row option-row--four photographer-edit-row" action="actions/salvar-fotografo.php" method="post" role="row">
                <input type="hidden" name="id" value="<?= e($fotografo['id']) ?>">
                <input type="hidden" name="tipo" value="fotografo">

                <span>
                  <input class="form-field form-field--small" type="text" name="nome" value="<?= e($fotografo['nome']) ?>" placeholder="Nome do fotógrafo" required>
                </span>

                <span>
                  <input class="form-field form-field--small js-phone-mask" type="text" inputmode="numeric" maxlength="15" pattern="\([0-9]{2}\) [0-9]{5}-[0-9]{4}" name="telefone" value="<?= e($fotografo['telefone'] ?? '') ?>">
                </span>

                <span>
                  <input class="form-field form-field--small" type="email" name="email" value="<?= e($fotografo['email'] ?? '') ?>">
                </span>

                <span class="option-actions photographer-actions">
                  <button class="action-soft-btn" type="submit">Salvar</button>
                  <button class="action-soft-btn" type="submit" formaction="actions/excluir-opcao.php" formmethod="post" name="tipo" value="fotografo" onclick="return confirm('Excluir este fotógrafo?');">Excluir</button>
                </span>
              </form>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    </main>
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
