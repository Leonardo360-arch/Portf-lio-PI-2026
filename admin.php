<?php require_once __DIR__ . '/src/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Danny</title>
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
          <span class="label">Painel administrativo</span>
          <h1 class="admin-title">Agenda e ensaios.</h1>
        </div>
        <a class="btn btn--primary" href="#novo-agendamento">Novo agendamento</a>
      </header>

      <section class="admin-metrics" aria-label="Resumo">
        <article class="metric-card">
          <span class="metric-card__label">Hoje</span>
          <strong class="metric-card__value">03</strong>
          <span class="metric-card__note">ensaios confirmados</span>
        </article>
        <article class="metric-card">
          <span class="metric-card__label">Histórico</span>
          <strong class="metric-card__value">128</strong>
          <span class="metric-card__note">atendimentos finalizados</span>
        </article>
        <article class="metric-card">
          <span class="metric-card__label">Solicitações</span>
          <strong class="metric-card__value">09</strong>
          <span class="metric-card__note">aguardando retorno</span>
        </article>
      </section>

      <section class="admin-section" id="agenda">
        <div class="admin-section__header">
          <div>
            <span class="label">Agenda</span>
            <h2 class="admin-section__title">Próximos ensaios</h2>
          </div>
          <button class="admin-filter" type="button">Maio 2026</button>
        </div>

        <div class="schedule-list">
          <article class="schedule-item">
            <time class="schedule-item__time" datetime="2026-05-11T09:00">
              <span>11/05</span>
              09:00
            </time>
            <div>
              <h3>Marina Costa</h3>
              <p>Ensaio feminino - Estúdio • Confirmado • R$ 650,00</p>
            </div>
            <span class="status status--confirmed">Confirmado</span>
          </article>
          <article class="schedule-item">
            <time class="schedule-item__time" datetime="2026-05-11T14:30">
              <span>11/05</span>
              14:30
            </time>
            <div>
              <h3>Bianca Lima</h3>
              <p>Gestante - Externo • Aguardando sinal • R$ 850,00</p>
            </div>
            <span class="status status--waiting">Aguardando</span>
          </article>
          <article class="schedule-item">
            <time class="schedule-item__time" datetime="2026-05-12T16:00">
              <span>12/05</span>
              16:00
            </time>
            <div>
              <h3>Ana Paula</h3>
              <p>Família - Parque • Confirmado • R$ 720,00</p>
            </div>
            <span class="status status--confirmed">Confirmado</span>
          </article>
        </div>
      </section>

      <section class="admin-section" id="novo-agendamento">
        <div class="admin-section__header">
          <div>
            <span class="label">Agenda</span>
            <h2 class="admin-section__title">Cadastrar atendimento</h2>
          </div>
        </div>

        <form class="admin-form" action="#" method="post">
          <label>
            Cliente
            <input class="form-field" type="text" name="cliente" placeholder="Nome da cliente" />
          </label>
          <label>
            Tipo de ensaio
            <input class="form-field" type="text" name="servico" placeholder="Gestante, família, feminino..." />
          </label>
          <label>
            Data
            <input class="form-field" type="date" name="data" />
          </label>
          <label>
            Horário
            <input class="form-field" type="time" name="horario" />
          </label>
          <label>
            Valor
            <input class="form-field" type="number" name="valor" min="0" step="0.01" placeholder="0,00" />
          </label>
          <label>
            Status
            <select class="form-field" name="status">
              <option value="confirmado">Confirmado</option>
              <option value="aguardando">Aguardando</option>
              <option value="atendido">Atendido</option>
            </select>
          </label>
          <button class="btn btn--primary admin-form__submit" type="submit">Salvar agendamento</button>
        </form>
      </section>

      <section class="admin-section" id="clientes-atendidos">
        <div class="admin-section__header">
          <div>
            <span class="label">Clientes atendidos</span>
            <h2 class="admin-section__title">Histórico de atendimentos</h2>
          </div>
          <div class="admin-month">
            <button class="admin-month__button" type="button" id="history-prev-month" aria-label="Ver mês anterior">‹</button>
            <strong class="admin-month__label" id="history-month-label">Maio 2026</strong>
            <button class="admin-month__button" type="button" id="history-next-month" aria-label="Ver próximo mês">›</button>
            <button class="admin-month__button admin-month__all" type="button" id="history-all">Todos</button>
          </div>
        </div>

        <div class="admin-history-tools">
          <strong class="admin-total" id="history-total">Total: R$ 0,00</strong>
          <div class="admin-sort" aria-label="Organizar histórico">
            <button class="admin-sort__button" type="button" data-sort="date">Data <span data-sort-icon="date">↓</span></button>
            <button class="admin-sort__button" type="button" data-sort="name">Nome <span data-sort-icon="name">↕</span></button>
            <button class="admin-sort__button" type="button" data-sort="value">Valor <span data-sort-icon="value">↕</span></button>
          </div>
        </div>

        <div class="admin-table" role="table" aria-label="Clientes atendidos">
          <div class="admin-table__row admin-table__row--head" role="row">
            <span>Cliente</span>
            <span>Ensaio</span>
            <span>Data</span>
            <span>Valor</span>
            <span>Status</span>
          </div>
          <div class="admin-table__body" id="history-table">
            <div class="admin-table__row" role="row" data-name="Camila Rocha" data-date="2026-05-02" data-value="650">
              <span>Camila Rocha</span>
              <span>Ensaio feminino</span>
              <span>02/05/2026</span>
              <span>R$ 650,00</span>
              <span class="status status--done">Atendida</span>
            </div>
            <div class="admin-table__row" role="row" data-name="Juliana Martins" data-date="2026-04-28" data-value="920">
              <span>Juliana Martins</span>
              <span>Gestante</span>
              <span>28/04/2026</span>
              <span>R$ 920,00</span>
              <span class="status status--done">Atendida</span>
            </div>
            <div class="admin-table__row" role="row" data-name="Renata Alves" data-date="2026-04-19" data-value="780">
              <span>Renata Alves</span>
              <span>Família</span>
              <span>19/04/2026</span>
              <span>R$ 780,00</span>
              <span class="status status--done">Atendida</span>
            </div>
            <div class="admin-table__row" role="row" data-name="Bruna Ferreira" data-date="2026-05-06" data-value="1200">
              <span>Bruna Ferreira</span>
              <span>Evento</span>
              <span>06/05/2026</span>
              <span>R$ 1.200,00</span>
              <span class="status status--done">Atendida</span>
            </div>
            <div class="admin-table__row" role="row" data-name="Patricia Souza" data-date="2026-03-22" data-value="540">
              <span>Patricia Souza</span>
              <span>Retratos profissionais</span>
              <span>22/03/2026</span>
              <span>R$ 540,00</span>
              <span class="status status--done">Atendida</span>
            </div>
          </div>
          <p class="admin-empty" id="history-empty" hidden>Nenhum atendimento neste mês.</p>
        </div>
      </section>

      <section class="admin-section" id="pedidos-agendamento">
        <div class="admin-section__header">
          <div>
            <span class="label">Clientes que querem agendar</span>
            <h2 class="admin-section__title">Solicitações recebidas</h2>
          </div>
        </div>

        <div class="request-grid">
          <article class="request-card">
            <span class="status status--new">Novo</span>
            <h3>Larissa Nunes</h3>
            <p>Quer orçamento para ensaio de gestante no fim de semana.</p>
            <div class="request-card__meta">
              <span>WhatsApp: (11) 99999-0000</span>
              <span>Preferência: sábado à tarde</span>
            </div>
            <div class="request-card__actions">
              <button class="btn btn--primary" type="button">Confirmar</button>
              <button class="btn btn--outline" type="button">Responder</button>
            </div>
          </article>

          <article class="request-card">
            <span class="status status--waiting">Pendente</span>
            <h3>Fernanda Melo</h3>
            <p>Pediu pacote para fotos de aniversário infantil.</p>
            <div class="request-card__meta">
              <span>WhatsApp: (21) 98888-0000</span>
              <span>Preferência: manhã</span>
            </div>
            <div class="request-card__actions">
              <button class="btn btn--primary" type="button">Confirmar</button>
              <button class="btn btn--outline" type="button">Responder</button>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>

  <script>
    const historyTable = document.querySelector('#history-table');
    const totalElement = document.querySelector('#history-total');
    const monthLabel = document.querySelector('#history-month-label');
    const emptyMessage = document.querySelector('#history-empty');
    const prevMonthButton = document.querySelector('#history-prev-month');
    const nextMonthButton = document.querySelector('#history-next-month');
    const allHistoryButton = document.querySelector('#history-all');
    const sortButtons = document.querySelectorAll('[data-sort]');
    const sortState = {
      key: 'date',
      direction: 'asc',
    };
    const currentMonth = new Date(2026, 4, 1);
    let showAllHistory = false;

    const formatCurrency = (value) => new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);

    const formatMonth = (date) => new Intl.DateTimeFormat('pt-BR', {
      month: 'long',
      year: 'numeric',
    }).format(date);

    const isSameMonth = (row, date) => {
      const rowDate = new Date(`${row.dataset.date}T00:00:00`);

      return rowDate.getFullYear() === date.getFullYear()
        && rowDate.getMonth() === date.getMonth();
    };

    const getVisibleRows = () => [...historyTable.querySelectorAll('.admin-table__row')]
      .filter((row) => !row.hidden);

    const updateTotal = () => {
      const rows = getVisibleRows();
      const total = rows.reduce((sum, row) => sum + Number(row.dataset.value || 0), 0);
      totalElement.textContent = `${rows.length} atendimento${rows.length === 1 ? '' : 's'} - Total: ${formatCurrency(total)}`;
      emptyMessage.hidden = rows.length > 0;
    };

    const updateSortIcons = () => {
      document.querySelectorAll('[data-sort-icon]').forEach((icon) => {
        icon.textContent = icon.dataset.sortIcon === sortState.key
          ? (sortState.direction === 'asc' ? '↑' : '↓')
          : '↕';
      });
    };

    const getSortValue = (row, key) => {
      if (key === 'value') {
        return Number(row.dataset.value || 0);
      }

      if (key === 'date') {
        return new Date(row.dataset.date).getTime();
      }

      return row.dataset.name.toLocaleLowerCase('pt-BR');
    };

    const renderHistory = () => {
      monthLabel.textContent = showAllHistory ? 'Lista completa' : formatMonth(currentMonth);
      const sortedRows = [...historyTable.querySelectorAll('.admin-table__row')].sort((current, next) => {
        const currentValue = getSortValue(current, sortState.key);
        const nextValue = getSortValue(next, sortState.key);

        if (typeof currentValue === 'number') {
          return sortState.direction === 'asc'
            ? currentValue - nextValue
            : nextValue - currentValue;
        }

        return sortState.direction === 'asc'
          ? currentValue.localeCompare(nextValue, 'pt-BR')
          : nextValue.localeCompare(currentValue, 'pt-BR');
      });

      historyTable.replaceChildren(...sortedRows);
      sortedRows.forEach((row) => {
        row.hidden = showAllHistory ? false : !isSameMonth(row, currentMonth);
      });
      allHistoryButton.classList.toggle('admin-month__all--active', showAllHistory);
      updateSortIcons();
      updateTotal();
    };

    const sortHistory = (key) => {
      sortState.direction = sortState.key === key && sortState.direction === 'asc' ? 'desc' : 'asc';
      sortState.key = key;
      renderHistory();
    };

    const changeMonth = (offset) => {
      showAllHistory = false;
      currentMonth.setMonth(currentMonth.getMonth() + offset);
      renderHistory();
    };

    sortButtons.forEach((button) => {
      button.addEventListener('click', () => sortHistory(button.dataset.sort));
    });

    prevMonthButton.addEventListener('click', () => changeMonth(-1));
    nextMonthButton.addEventListener('click', () => changeMonth(1));
    allHistoryButton.addEventListener('click', () => {
      showAllHistory = true;
      renderHistory();
    });

    renderHistory();
  </script>
</body>
</html>
