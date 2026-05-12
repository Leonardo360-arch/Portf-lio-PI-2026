<?php require_once __DIR__ . '/src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Criar Conta — Danny Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body class="auth-page">
  <main class="auth">
    <section class="auth__panel">
      <a class="auth__brand" href="index.php">Danny</a>
      <span class="label">Novo acesso</span>
      <h1 class="auth__title">Criar conta.</h1>
      <p class="auth__text">Cadastro inicial para preparar o painel administrativo. A validação e permissões podem ser conectadas depois no PHP.</p>

      <form class="auth-form" action="#" method="post">
        <label class="auth-form__group">
          <span>Nome</span>
          <input class="form-field" type="text" name="nome" placeholder="Seu nome" autocomplete="name" required />
        </label>
        <label class="auth-form__group">
          <span>E-mail</span>
          <input class="form-field" type="email" name="email" placeholder="seu@email.com" autocomplete="email" required />
        </label>
        <label class="auth-form__group">
          <span>Senha</span>
          <input class="form-field" type="password" name="senha" placeholder="Crie uma senha" autocomplete="new-password" required />
        </label>
        <label class="auth-form__group">
          <span>Confirmar senha</span>
          <input class="form-field" type="password" name="confirmar_senha" placeholder="Repita a senha" autocomplete="new-password" required />
        </label>
        <button class="btn btn--submit" type="submit">Criar conta</button>
      </form>

      <p class="auth__switch">Já tem conta? <a href="login.php">Entrar</a></p>
    </section>
  </main>
</body>
</html>
