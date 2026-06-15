<nav class="nav" id="siteNav">
  <a class="nav__logo" href="index.php">DannyDuarte</a>

  <ul class="nav__links" id="siteNavLinks">
    <li><a href="#inicio" class="active">Inicio</a></li>
    <li><a href="#sobre">Sobre</a></li>
    <!-- <li><a href="#cenarios">Cenarios</a></li> -->
    <li><a href="#contato">Contato</a></li>
    <li><a href="cadastro-cliente.php">Cadastro</a></li>
    <li><a href="login-cliente.php">Cliente</a></li>
  </ul>

  <button class="nav__toggle" type="button" aria-label="Abrir menu" aria-controls="siteNavLinks" aria-expanded="false">
    &#9776;
  </button>
</nav>

<script>
  document.querySelector('.nav__toggle')?.addEventListener('click', function () {
    const nav = document.getElementById('siteNav');
    const isOpen = nav?.classList.toggle('nav--open') ?? false;

    this.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });
</script>
