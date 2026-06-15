<?php require_once __DIR__ . '/src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recuperar senha - Danny</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-page">
  <main class="auth">
    <section class="auth__panel">
      <a class="auth__brand" href="index.php">Danny</a>
      <span class="label">Recuperacao de senha</span>
      <h1 class="auth__title">Criar nova senha.</h1>
      <p class="auth__text">Informe o e-mail cadastrado para receber um link seguro de redefinicao.</p>

      <?php if (isset($_GET['status']) && $_GET['status'] === 'enviado'): ?>
        <p class="auth-alert auth-alert--success">Se o e-mail estiver cadastrado, enviaremos o link de recuperacao.</p>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <p class="auth-alert auth-alert--error">
          <?php
            echo match ($_GET['erro']) {
              'email' => 'Informe um e-mail valido.',
              'envio' => 'Nao foi possivel enviar o e-mail agora. Confira as configuracoes SMTP.',
              'token' => 'Solicite um novo link de recuperacao.',
              default => 'Ocorreu um erro. Tente novamente.',
            };
          ?>
        </p>
      <?php endif; ?>

      <form class="auth-form" action="actions/solicitar-recuperacao-senha.php" method="post">
        <label class="auth-form__group">
          <span>E-mail</span>
          <input class="form-field" type="email" name="email" placeholder="seu@email.com" autocomplete="email" required />
        </label>
        <button class="btn btn--submit" type="submit">Enviar link</button>
      </form>

      <p class="auth__switch"><a href="login-cliente.php">Voltar para login do cliente</a></p>
    </section>
  </main>
</body>
</html>
