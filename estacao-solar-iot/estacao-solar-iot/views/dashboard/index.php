<?php /** $resumo vem do DashboardService; o JS atualiza tudo a cada 5s via /api/dashboard */ ?>
<h1>Painel ao vivo</h1>

<?php if (!$resumo['estacao']): ?>
  <section class="vazio"><p>Nenhuma estação ativa cadastrada ainda.</p></section>
<?php else: ?>
<div class="linha-topo">
  <label class="inline-label">Estação
    <select id="seletor-estacao">
      <?php foreach ($resumo['estacoes'] as $e): ?>
        <option value="<?= $e['id'] ?>" <?= sel($e['id'], $resumo['estacao']['id']) ?>><?= e($e['nome']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <span id="status" class="selo selo-off">carregando…</span>
  <span class="mudo" id="ultimo-contato"></span>
</div>

<section class="grade-cards">
  <article class="metrica metrica-temp"><span class="rotulo">🌡️ Temperatura</span><strong id="v-temp">—</strong><span class="un">°C</span><small id="s-temp"></small></article>
  <article class="metrica metrica-umid"><span class="rotulo">💧 Umidade</span><strong id="v-umid">—</strong><span class="un">%</span><small id="s-umid"></small></article>
  <article class="metrica metrica-luz"><span class="rotulo">☀️ Luminosidade</span><strong id="v-luz">—</strong><span class="un">%</span><small id="s-luz"></small></article>
  <article class="metrica metrica-tot"><span class="rotulo">📊 No sistema</span>
    <ul class="totais">
      <li><b id="t-est"><?= $resumo['totais']['estacoes'] ?></b> estações</li>
      <li><b id="t-lei"><?= $resumo['totais']['leituras'] ?></b> leituras</li>
      <li><b id="t-vis"><?= $resumo['totais']['visitantes'] ?></b> visitantes</li>
    </ul></article>
</section>

<section class="grade-graficos">
  <div class="cartao"><h2>Temperatura (°C)</h2><canvas id="g-temp" height="140"></canvas></div>
  <div class="cartao"><h2>Umidade (%)</h2><canvas id="g-umid" height="140"></canvas></div>
  <div class="cartao"><h2>Luminosidade (%)</h2><canvas id="g-luz" height="140"></canvas></div>
</section>

<script>window.APP_BASE = <?= json_encode(\App\Core\App::$base) ?>; window.ESTACAO_INICIAL = <?= (int) $resumo['estacao']['id'] ?>;</script>
<script src="<?= url('/assets/js/chart.umd.min.js') ?>"></script>
<script src="<?= url('/assets/js/dashboard.js') ?>"></script>
<?php endif; ?>
