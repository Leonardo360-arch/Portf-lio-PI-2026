<?php
require_once __DIR__ . '/src/bootstrap.php';

$token = trim($_GET['token'] ?? '');
$tokenInvalido = isset($_GET['erro']) && $_GET['erro'] === 'token';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Redefinir senha - Danny</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-page">
  <main class="auth">
    <section class="auth__panel">
      <a class="auth__brand" href="index.php">Danny</a>
      <span class="label">Nova senha</span>
      <h1 class="auth__title">Redefinir acesso.</h1>
      <p class="auth__text">Crie uma senha nova para voltar a acessar sua conta.</p>

      <?php if ($token === '' || $tokenInvalido): ?>
        <p class="auth-alert auth-alert--error">Link invalido ou expirado. Solicite uma nova recuperacao de senha.</p>
        <p class="auth__switch"><a href="esqueci-senha.php">Solicitar novo link</a></p>
      <?php else: ?>
        <?php if (isset($_GET['erro'])): ?>
          <p class="auth-alert auth-alert--error">
            <?php
              echo match ($_GET['erro']) {
                'senha_curta' => 'A senha precisa ter pelo menos 6 caracteres.',
                'senhas' => 'As senhas nao conferem.',
                'banco' => 'Nao foi possivel atualizar a senha agora.',
                default => 'Ocorreu um erro. Tente novamente.',
              };
            ?>
          </p>
        <?php endif; ?>

        <form class="auth-form" action="actions/redefinir-senha.php" method="post">
          <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" />
          <label class="auth-form__group">
            <span>Nova senha</span>
            <input class="form-field" type="password" name="senha" placeholder="Digite uma nova senha" autocomplete="new-password" minlength="6" required />
          </label>
          <label class="auth-form__group">
            <span>Confirmar senha</span>
            <input class="form-field" type="password" name="confirmar_senha" placeholder="Repita a nova senha" autocomplete="new-password" minlength="6" required />
          </label>
          <button class="btn btn--submit" type="submit">Salvar nova senha</button>
        </form>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
