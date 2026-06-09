<?php
require_once __DIR__ . '/src/bootstrap.php';

if (!function_exists('e')) {
  function e(mixed $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }
}

$telefoneWhatsapp = '5519992132024';
$mensagem = 'Olá, vim pelo site e gostaria de saber mais sobre os ensaios fotográficos.';
$linkWhatsapp = 'https://wa.me/' . $telefoneWhatsapp . '?text=' . urlencode($mensagem);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Danny — Beleza & Presença</title>

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
      <h1 class="hero__title">Beleza,<br>presença e<br>confiança com<br>assinatura<br>pessoal.</h1>

      <p class="hero__sub">
        Hero mais explicativo, com promessa clara, chamada para agendamento e acesso rápido à
        história da profissional, sem obrigar o usuário a sair da primeira página.
      </p>

      <div class="hero__ctas">
        <a href="cadastro-cliente.php" class="btn btn--primary">Criar cadastro</a>
        <a href="login-cliente.php" class="btn btn--outline">Área do cliente</a>
      </div>
    </div>

    <div class="hero__image">
      <div class="placeholder">
        <span>Imagem principal<br>Retrato / Ambiente</span>
      </div>
    </div>
  </section>


  <section class="sobre" id="sobre">
    <div class="sobre__card">
      <span class="label">Sobre a Danny</span>

      <h2 class="sobre__title">Uma<br>apresentação<br>curta, humana e<br>posicionadora.</h2>

      <p class="sobre__text">
        Em vez de manter o "Sobre a Danny" como uma página isolada, a seção passa a aparecer logo
        após o hero. A recomendação é usar um texto breve com origem, especialidade, experiência e
        diferencial de atendimento.
      </p>

      <p class="sobre__text">
        Esse posicionamento cria autoridade cedo, reforça a confiança do visitante e prepara melhor
        o clique para WhatsApp ou para os cards/serviços.
      </p>

      <div class="sobre__stats">
        <div class="stat">
          <span class="stat__num">01</span>
          <span class="stat__label">Especialidade no<br>foco principal</span>
        </div>

        <div class="stat">
          <span class="stat__num">02</span>
          <span class="stat__label">Anos de<br>experiência e<br>formação</span>
        </div>

        <div class="stat">
          <span class="stat__num">03</span>
          <span class="stat__label">Diferencial no<br>atendimento</span>
        </div>
      </div>
    </div>

    <div class="sobre__image">
      <span class="sobre__image-label">Posição principal "Sobre mi" estava na primeira página</span>

      <div class="placeholder">
        <span>Imagem Danny</span>
      </div>
    </div>
  </section>


  <section class="cenarios" id="cenarios">
    <div class="cenarios__intro">
      <span class="label">Cenários</span>

      <h2 class="cenarios__title">Organizar os<br>caminhos de<br>escolha da<br>cliente.</h2>

      <p class="cenarios__text">
        A seção de cenários pode substituir uma navegação solta por três entradas claras. Cada
        card deve explicar para quem é, qual transformação oferece e levar para contato ou detalhe do serviço.
      </p>

      <p class="cenarios__note">
        No mobile, esses cards devem empilhar em sequência com CTA individual no fim de cada card.
      </p>
    </div>

    <div class="cenarios__cards">
      <div class="cenario-card">
        <span class="cenario-card__label">Cenário 1</span>

        <div class="placeholder placeholder--sm">
          <span>Imagem/Ícone +<br>nome/descrição + CTA</span>
        </div>
      </div>

      <div class="cenario-card">
        <span class="cenario-card__label">Cenário 2</span>

        <div class="placeholder placeholder--sm">
          <span>Imagem/Ícone +<br>nome/descrição + CTA</span>
        </div>
      </div>

      <div class="cenario-card">
        <span class="cenario-card__label">Cenário 3</span>

        <div class="placeholder placeholder--sm">
          <span>Imagem/Ícone +<br>nome/descrição + CTA</span>
        </div>
      </div>
    </div>
  </section>


  <section class="galeria">
    <span class="label">Portfólio / Resultados</span>

    <h2 class="galeria__title">Galeria enxuta para reforçar<br>prova visual.</h2>

    <div class="galeria__grid">
      <div class="placeholder placeholder--gal">
        <span>Imagem 1</span>
      </div>

      <div class="placeholder placeholder--gal">
        <span>Imagem 2</span>
      </div>

      <div class="placeholder placeholder--gal">
        <span>Imagem 3</span>
      </div>

      <div class="placeholder placeholder--gal">
        <span>Imagem 4</span>
      </div>
    </div>
  </section>


  <section class="depoimento">
    <div class="depoimento__card">
      <span class="label">Experiência</span>

      <blockquote class="depoimento__quote">
        "Espaço reservado para frase de impacto, depoimento curto ou mensagem sobre a experiência de atendimento da
        Danny."
      </blockquote>

      <p class="depoimento__note">Essa seção reaproveitou o item do card central do wireframe original.</p>
    </div>
  </section>


  <section class="contato" id="contato">
    <div class="contato__intro">
      <span class="label">Contato</span>

      <h2 class="contato__title">Fechamento com<br>CTA claro e<br>formulário simples.</h2>

      <p class="contato__text">
        Manter o formulário, mas posicionar WhatsApp como conversa principal e formulário como
        alternativa para leads mais detalhados.
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
          Enviar formulário no WhatsApp
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
        'Olá, vim pelo site e gostaria de falar sobre os ensaios fotográficos.',
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
