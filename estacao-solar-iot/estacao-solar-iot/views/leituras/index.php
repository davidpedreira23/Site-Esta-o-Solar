<?php /** $r (itens,total,pagina,paginas), $filtro, $estacoes */
$qs = fn(int $pg) => url('/leituras?' . http_build_query(array_filter(['estacao' => $filtro, 'pagina' => $pg]))); ?>
<div class="linha-topo">
  <h1>Leituras <small class="mudo">(<?= $r['total'] ?>)</small></h1>
  <a class="btn" href="<?= url('/leituras/novo' . ($filtro ? "?estacao=$filtro" : '')) ?>">+ Leitura manual</a>
</div>
<form method="get" action="<?= url('/leituras') ?>" class="filtro">
  <label class="inline-label">Estação
    <select name="estacao" onchange="this.form.submit()">
      <option value="">Todas</option>
      <?php foreach ($estacoes as $e): ?>
        <option value="<?= $e['id'] ?>" <?= sel($e['id'], $filtro) ?>><?= e($e['nome']) ?></option>
      <?php endforeach; ?>
    </select></label>
</form>
<?php if (!$r['itens']): ?>
  <section class="vazio"><p>Nenhuma leitura encontrada.</p></section>
<?php else: ?>
<div class="tabela-wrap"><table>
  <thead><tr><th>#</th><th>Estação</th><th>Temp.</th><th>Umid.</th><th>Luz</th><th>LDR bruto</th><th>Origem</th><th>Data/hora</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($r['itens'] as $l): ?>
    <tr>
      <td><?= $l['id'] ?></td>
      <td><?= e($l['estacao_nome']) ?></td>
      <td><?= number_format((float) $l['temperatura'], 1, ',', '') ?> °C</td>
      <td><?= number_format((float) $l['umidade'], 1, ',', '') ?> %</td>
      <td><?= (int) $l['luminosidade'] ?> %</td>
      <td><?= $l['ldr_bruto'] ?? '—' ?></td>
      <td><span class="selo <?= $l['origem'] === 'esp32' ? 'selo-on' : 'selo-warn' ?>"><?= e($l['origem']) ?></span></td>
      <td><?= data_br($l['registrada_em'], true) ?></td>
      <td class="acoes">
        <a class="btn-sm" href="<?= url('/leituras/' . $l['id'] . '/editar') ?>">Editar</a>
        <form method="post" action="<?= url('/leituras/' . $l['id'] . '/excluir') ?>" class="inline"
              onsubmit="return confirm('Excluir esta leitura?')">
          <?= csrf_field() ?><button class="btn-sm btn-perigo" type="submit">Excluir</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<nav class="paginacao">
  <?php if ($r['pagina'] > 1): ?><a href="<?= $qs($r['pagina'] - 1) ?>">← Anterior</a><?php endif; ?>
  <span>Página <?= $r['pagina'] ?> de <?= $r['paginas'] ?></span>
  <?php if ($r['pagina'] < $r['paginas']): ?><a href="<?= $qs($r['pagina'] + 1) ?>">Próxima →</a><?php endif; ?>
</nav>
<?php endif; ?>
