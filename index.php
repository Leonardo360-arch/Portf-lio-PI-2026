<?php
require_once __DIR__ . '/src/bootstrap.php';

if (!function_exists('e')) {
  function e(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

$telefoneWhatsapp = '5519992132024';
$mensagem = 'Ola, vim pelo site e gostaria de saber mais sobre os ensaios fotograficos.';
$linkWhatsapp = 'https://wa.me/' . $telefoneWhatsapp . '?text=' . urlencode($mensagem);

$servicos = [
  [
    'titulo' => 'Newborn',
    'texto' => 'Ensaios delicados para registrar os primeiros dias com calma, cuidado e acolhimento.',
    'imagem' => 'img/NEWBORN/IMG_9790.jpg',
  ],
  [
    'titulo' => 'Gestante',
    'texto' => 'Retratos de espera com direcao leve, cenarios preparados e uma experiencia tranquila.',
    'imagem' => 'img/GESTANTE/VITORIA%20(145).jpg',
  ],
  [
    'titulo' => 'Smash',
    'texto' => 'Um ensaio colorido e afetivo para celebrar aniversarios, descobertas e muita expressao.',
    'imagem' => 'img/SMASH/LIVIA%20(7).jpg',
  ],
];

$galeria = [
  ['src' => 'img/ACOMPANHAMENTO%20MENSAL/LIVIA%20(9).jpg', 'alt' => 'Ensaio acompanhamento mensal'],
  ['src' => 'img/COMEMORATIVO/HELENA%20(7).jpg', 'alt' => 'Ensaio comemorativo'],
  ['src' => 'img/NEWBORN/KAUAN%20(12).jpg', 'alt' => 'Ensaio newborn'],
  ['src' => 'img/SMASH/ANA%20LAURA%20(31).jpg', 'alt' => 'Ensaio smash'],
  ['src' => 'img/GESTANTE/VITORIA%20(208)-Editar.jpg', 'alt' => 'Ensaio gestante'],
  ['src' => 'img/EMPRESARIAL/1691236522392.jpg', 'alt' => 'Retrato empresarial'],
  ['src' => 'img/ACOMPANHAMENTO%20MENSAL/RAVI%20(120)%20copiar.jpg', 'alt' => 'Ensaio infantil'],
  ['src' => 'img/NEWBORN/LUNNA%20(12).jpg', 'alt' => 'Detalhe de ensaio newborn'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Danny - Ensaios Fotograficos</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

  <link
    href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <?php include __DIR__ . '/includes/site-header.php'; ?>

  <section class="hero" id="inicio">
    <div class="hero__content">
      <span class="label">Fotografia afetiva</span>
      <h1 class="hero__title">Ensaios para guardar fases que passam rapido.</h1>

      <p class="hero__sub">
        Newborn, gestante, infantil, comemorativo e retratos profissionais com direcao sensivel,
        cenarios pensados e atendimento proximo do primeiro contato ate a entrega.
      </p>

      <div class="hero__ctas">
        <a href="cadastro-cliente.php" class="btn btn--primary">Criar cadastro</a>
        <a href="<?= e($linkWhatsapp) ?>" class="btn btn--outline" target="_blank" rel="noopener">Falar no WhatsApp</a>
      </div>
    </div>

    <div class="hero__image photo-frame photo-frame--hero">
      <img src="img/DANI/_DSC6383.jpg" alt="Danny em ensaio fotografico" />
    </div>
  </section>

  <section class="sobre" id="sobre">
    <div class="sobre__card">
      <span class="label">Sobre a Danny</span>

      <h2 class="sobre__title">Um olhar cuidadoso para cada historia.</h2>

      <p class="sobre__text">
        A fotografia aqui e feita para acolher, dirigir com leveza e transformar momentos simples em
        lembrancas bonitas de revisitar. Cada ensaio recebe uma proposta alinhada ao perfil da familia,
        da crianca, da gestante ou da marca.
      </p>

      <p class="sobre__text">
        O objetivo e criar uma experiencia confortavel, com orientacao durante todo o processo e imagens
        que carreguem personalidade sem perder a delicadeza.
      </p>

    </div>

    <div class="sobre__image">
      <span class="sobre__image-label">Retratos, familias, bebes e marcas</span>

      <div class="photo-frame photo-frame--about">
        <img src="img/DANI/_DSC6381.jpg" alt="Retrato da Danny" />
      </div>
    </div>
  </section>

  <section class="cenarios" id="cenarios">
    <div class="cenarios__intro">
      <span class="label">Ensaios</span>

      <h2 class="cenarios__title">Escolha a experiencia que combina com seu momento.</h2>

      <p class="cenarios__text">
        As categorias ajudam voce a iniciar o pedido de agendamento com mais clareza. Depois do cadastro,
        o painel do cliente permite acompanhar suas solicitacoes.
      </p>
    </div>

    <div class="cenarios__cards">
      <?php foreach ($servicos as $servico): ?>
        <article class="cenario-card">
          <div class="cenario-card__image">
            <img src="<?= e($servico['imagem']) ?>" alt="<?= e($servico['titulo']) ?>" />
          </div>
          <span class="cenario-card__label"><?= e($servico['titulo']) ?></span>
          <p class="cenario-card__text"><?= e($servico['texto']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="galeria">
    <span class="label">Portfolio</span>

    <h2 class="galeria__title">Um pouco do que ja passou pelas lentes.</h2>

    <div class="galeria__grid">
      <?php foreach ($galeria as $foto): ?>
        <figure class="galeria__item">
          <img src="<?= e($foto['src']) ?>" alt="<?= e($foto['alt']) ?>" loading="lazy" />
        </figure>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="depoimento">
    <div class="depoimento__card">
      <span class="label">Experiencia</span>

      <blockquote class="depoimento__quote">
        "Fotografar tambem e cuidar do tempo: preparar o encontro, respeitar o ritmo e entregar imagens
        que continuem dizendo algo depois."
      </blockquote>

      <p class="depoimento__note">Agende pelo cadastro do cliente ou chame no WhatsApp para tirar duvidas.</p>
    </div>
  </section>

  <section class="contato" id="contato">
    <div class="contato__intro">
      <span class="label">Contato</span>

      <h2 class="contato__title">Vamos planejar seu ensaio?</h2>

      <p class="contato__text">
        Envie uma mensagem com o tipo de ensaio, data desejada e qualquer detalhe importante. Se preferir,
        fale direto pelo WhatsApp.
      </p>
    </div>

    <form class="contato__form" action="actions/contact.php" method="post" id="form-contato">
      <input
        class="form-field"
        type="text"
        name="nome"
        placeholder="Nome"
        autocomplete="name"
        required
      />

      <input
        class="form-field"
        type="email"
        name="email"
        placeholder="E-mail"
        autocomplete="email"
        required
      />

      <input
        class="form-field"
        type="tel"
        name="telefone"
        placeholder="Telefone / WhatsApp"
        autocomplete="tel"
        required
      />

      <textarea
        class="form-field form-field--textarea"
        name="mensagem"
        placeholder="Mensagem"
        required
      ></textarea>

      <div class="contato__actions">
        <button class="btn btn--outline" type="button" id="btn-whatsapp-form">
          Enviar formulario no WhatsApp
        </button>

        <a
          class="btn btn--primary contato__whatsapp-direct"
          href="<?= e($linkWhatsapp) ?>"
          target="_blank"
          rel="noopener"
        >
          Falar direto no WhatsApp
        </a>
      </div>
    </form>
  </section>

  <?php include __DIR__ . '/includes/site-footer.php'; ?>

  <a href="<?= e($linkWhatsapp) ?>" class="whatsapp-btn" target="_blank" rel="noopener" aria-label="WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
      <path
        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
      <path
        d="M12 0C5.373 0 0 5.373 0 12c0 2.125.557 4.126 1.532 5.864L.057 23.428a.5.5 0 0 0 .611.625l5.757-1.507A11.952 11.952 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.012-1.373l-.36-.214-3.716.973.992-3.624-.234-.374A9.818 9.818 0 1 1 12 21.818z" />
    </svg>
  </a>

  <script>
    const telefoneWhatsappSite = '<?= e($telefoneWhatsapp) ?>';
    const formContato = document.getElementById('form-contato');
    const botaoWhatsappForm = document.getElementById('btn-whatsapp-form');

    function montarMensagemWhatsapp() {
      const nome = formContato?.querySelector('[name="nome"]')?.value.trim() || '';
      const email = formContato?.querySelector('[name="email"]')?.value.trim() || '';
      const telefone = formContato?.querySelector('[name="telefone"]')?.value.trim() || '';
      const mensagem = formContato?.querySelector('[name="mensagem"]')?.value.trim() || '';

      return [
        'Ola, vim pelo site e gostaria de falar sobre os ensaios fotograficos.',
        '',
        nome ? `Nome: ${nome}` : '',
        email ? `E-mail: ${email}` : '',
        telefone ? `Telefone: ${telefone}` : '',
        mensagem ? `Mensagem: ${mensagem}` : ''
      ].filter(Boolean).join('\n');
    }

    botaoWhatsappForm?.addEventListener('click', () => {
      if (!formContato.checkValidity()) {
        formContato.reportValidity();
        return;
      }

      const texto = encodeURIComponent(montarMensagemWhatsapp());
      const link = `https://wa.me/${telefoneWhatsappSite}?text=${texto}`;

      window.open(link, '_blank', 'noopener');
    });
  </script>

</body>

</html>
