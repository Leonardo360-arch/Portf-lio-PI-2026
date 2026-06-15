<?php require_once __DIR__ . '/src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Login Cliente - Danny</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />

  <style>
    .admin-hidden-link {
      position: fixed;
      right: 12px;
      bottom: 12px;
      width: 35px;
      height: 35px;
      opacity: 0;
      z-index: 9999;
      cursor: default;
    }

    .auth-form__remember {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 2px 0 8px;
      font-size: 0.95rem;
      color: inherit;
      cursor: pointer;
      user-select: none;
    }

    .auth-form__remember input {
      width: 16px;
      height: 16px;
      cursor: pointer;
    }
  </style>
</head>

<body class="auth-page">
  <main class="auth">
    <section class="auth__panel">
      <a class="auth__brand" href="index.php">Danny</a>

      <span class="label">Area do cliente</span>

      <h1 class="auth__title">Entrar como cliente.</h1>

      <p class="auth__text">
        Acesse para solicitar agendamento e acompanhar suas solicitacoes.
      </p>

      <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso'): ?>
        <p class="auth-alert auth-alert--success">
          Cadastro criado com sucesso. Faca login para continuar.
        </p>
      <?php endif; ?>

      <?php if (isset($_GET['senha']) && $_GET['senha'] === 'alterada'): ?>
        <p class="auth-alert auth-alert--success">
          Senha alterada com sucesso. Entre com a nova senha.
        </p>
      <?php endif; ?>

      <?php if (isset($_GET['erro'])): ?>
        <p class="auth-alert auth-alert--error">
          <?php
            echo match ($_GET['erro']) {
              'campos' => 'Preencha e-mail e senha.',
              'login' => 'E-mail ou senha invalidos.',
              'banco' => 'Erro ao acessar o banco.',
              default => 'Ocorreu um erro. Tente novamente.',
            };
          ?>
        </p>
      <?php endif; ?>

      <form class="auth-form" action="actions/login-cliente.php" method="post">
        <label class="auth-form__group">
          <span>E-mail</span>
          <input
            class="form-field"
            type="email"
            name="email"
            placeholder="seu@email.com"
            autocomplete="email"
            required
          />
        </label>

        <label class="auth-form__group">
          <span>Senha</span>
          <input
            class="form-field"
            type="password"
            name="senha"
            placeholder="Digite sua senha"
            autocomplete="current-password"
            required
          />
        </label>

        <label class="auth-form__remember">
          <input type="checkbox" name="lembrar" value="1" />
          <span>Lembrar acesso</span>
        </label>

        <button class="btn btn--submit" type="submit">
          Entrar
        </button>
      </form>

      <p class="auth__switch">
        <a href="esqueci-senha.php">Esqueci minha senha</a>
      </p>

      <p class="auth__switch">
        Ainda nao tem cadastro? <a href="cadastro-cliente.php">Cadastrar-se</a>
      </p>

      <a
        class="admin-hidden-link"
        href="login.php"
        aria-label="Acesso administrativo"
        title=""
      ></a>
    </section>
  </main>
</body>
</html>