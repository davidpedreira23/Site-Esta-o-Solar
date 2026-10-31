/**
 * DASHBOARD — busca /api/dashboard a cada 5 segundos e atualiza cards + gráficos.
 * Não recarrega a página: usa fetch() (AJAX) e o Chart.js (arquivo local).
 */
(function () {
  const BASE = window.APP_BASE || '';
  const $ = (id) => document.getElementById(id);
  let estacao = window.ESTACAO_INICIAL;
  const fmt = (n, d = 1) => (n === null || n === undefined ? '—' : Number(n).toFixed(d).replace('.', ','));

  // Cria um gráfico de linha simples
  function criarGrafico(id, cor, min, max) {
    return new Chart($(id), {
      type: 'line',
      data: { labels: [], datasets: [{ data: [], borderColor: cor, backgroundColor: cor + '22', fill: true, tension: .35, pointRadius: 0, borderWidth: 2 }] },
      options: {
        animation: false, plugins: { legend: { display: false } },
        scales: { x: { ticks: { maxTicksLimit: 6 }, grid: { display: false } }, y: { suggestedMin: min, suggestedMax: max } },
      },
    });
  }
  const gTemp = criarGrafico('g-temp', '#ef4444');
  const gUmid = criarGrafico('g-umid', '#0ea5e9', 0, 100);
  const gLuz  = criarGrafico('g-luz',  '#f59e0b', 0, 100);

  function preencher(g, labels, valores) {
    g.data.labels = labels; g.data.datasets[0].data = valores; g.update();
  }

  async function atualizar() {
    try {
      const r = await fetch(`${BASE}/api/dashboard?estacao=${estacao}`);
      const d = await r.json();
      if (!d.estacao) return;

      const u = d.ultima, s = d.stats;
      $('v-temp').textContent = u ? fmt(u.temperatura) : '—';
      $('v-umid').textContent = u ? fmt(u.umidade) : '—';
      $('v-luz').textContent  = u ? fmt(u.luminosidade, 0) : '—';
      $('s-temp').textContent = s && s.n ? `24h: mín ${fmt(s.temp_min)}° · máx ${fmt(s.temp_max)}°` : '';
      $('s-umid').textContent = s && s.n ? `média 24h: ${fmt(s.umid_media)} %` : '';
      $('s-luz').textContent  = s && s.n ? `média 24h: ${fmt(s.luz_media, 0)} %` : '';

      $('t-est').textContent = d.totais.estacoes;
      $('t-lei').textContent = d.totais.leituras;
      $('t-vis').textContent = d.totais.visitantes;

      const st = $('status');
      st.textContent = d.estacao.online ? '● online' : '● offline';
      st.className = 'selo ' + (d.estacao.online ? 'selo-on' : 'selo-warn');
      $('ultimo-contato').textContent = d.estacao.ultimo_contato ? 'último dado: ' + d.estacao.ultimo_contato.replace(/^\d{4}-(\d\d)-(\d\d) /, '$2/$1 ') : 'sem dados ainda';

      preencher(gTemp, d.serie.labels, d.serie.temperatura);
      preencher(gUmid, d.serie.labels, d.serie.umidade);
      preencher(gLuz,  d.serie.labels, d.serie.luminosidade);
    } catch (e) {
      const st = $('status'); st.textContent = 'sem conexão'; st.className = 'selo selo-off';
    }
  }

  $('seletor-estacao').addEventListener('change', (ev) => { estacao = ev.target.value; atualizar(); });
  atualizar();
  setInterval(atualizar, 5000);
})();
