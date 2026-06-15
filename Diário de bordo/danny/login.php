<?php require_once __DIR__ . '/src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Danny Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-page">
  <main class="auth">
    <section class="auth__panel">
      <a class="auth__brand" href="index.php">Danny</a>
      <span class="label">Área administrativa</span>
      <h1 class="auth__title">Entrar na conta.</h1>
      <p class="auth__text">Acesso reservado para gerenciar agenda, clientes atendidos e solicitações de atendimento.</p>

      <form class="auth-form" action="actions/login.php" method="post">
        <label class="auth-form__group">
          <span>E-mail</span>
          <input class="form-field" type="email" name="email" placeholder="seu@email.com" autocomplete="email" required />
        </label>
        <label class="auth-form__group">
          <span>Senha</span>
          <input class="form-field" type="password" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required />
        </label>
        <div class="auth-form__row">
          <label class="auth-form__check">
            <input type="checkbox" name="lembrar" />
            <span>Lembrar acesso</span>
          </label>
          <a href="#">Esqueci minha senha</a>
        </div>
        <button class="btn btn--submit" type="submit">Entrar</button>
      </form>

      <p class="auth__switch">Ainda não tem conta? <a href="criar-conta.php">Criar conta</a></p>
    </section>
  </main>

</body>
</html>
