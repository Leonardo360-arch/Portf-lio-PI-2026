<?php require_once __DIR__ . '/src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Cliente — Danny</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-page">
  <main class="auth">
    <section class="auth__panel">
      <a class="auth__brand" href="index.php">Danny</a>
      <span class="label">Área do cliente</span>
      <h1 class="auth__title">Entrar como cliente.</h1>
      <p class="auth__text">Acesse para solicitar agendamento e acompanhar suas solicitações.</p>

      <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso'): ?>
        <p style="color: green; font-weight: bold;">Cadastro criado com sucesso. Faça login para continuar.</p>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <p style="color: red; font-weight: bold;">
          <?php
            echo match ($_GET['erro']) {
              'campos' => 'Preencha e-mail e senha.',
              'login' => 'E-mail ou senha inválidos.',
              'banco' => 'Erro ao acessar o banco.',
              default => 'Ocorreu um erro. Tente novamente.',
            };
          ?>
        </p>
      <?php endif; ?>

      <form class="auth-form" action="actions/login-cliente.php" method="post">
        <label class="auth-form__group">
          <span>E-mail</span>
          <input class="form-field" type="email" name="email" placeholder="seu@email.com" autocomplete="email" required />
        </label>
        <label class="auth-form__group">
          <span>Senha</span>
          <input class="form-field" type="password" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required />
        </label>
        <button class="btn btn--submit" type="submit">Entrar</button>
      </form>

      <p class="auth__switch">Ainda não tem cadastro? <a href="cadastro-cliente.php">Cadastrar-se</a></p>
      <p class="auth__switch"><a href="login.php">Entrar como administrador</a></p>
    </section>
  </main>
</body>
</html>
