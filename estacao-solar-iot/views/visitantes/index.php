<?php /** $r, $busca */
$qs = fn(int $pg) => url('/visitantes?' . http_build_query(array_filter(['q' => $busca, 'pagina' => $pg]))); ?>
<div class="linha-topo">
  <h1>Visitantes <small class="mudo">(<?= $r['total'] ?>)</small></h1>
  <a class="btn" href="<?= url('/visitantes/novo') ?>">+ Novo visitante</a>
</div>
<form method="get" action="<?= url('/visitantes') ?>" class="filtro">
  <input type="search" name="q" value="<?= e($busca) ?>" placeholder="Buscar por nome, e-mail ou instituição…">
  <button class="btn btn-sec" type="submit">Buscar</button>
</form>
<?php if (!$r['itens']): ?>
  <section class="vazio"><p>Nenhum visitante encontrado.</p></section>
<?php else: ?>
<div class="tabela-wrap"><table>
  <thead><tr><th>#</th><th>Nome</th><th>E-mail</th><th>Instituição</th><th>Cidade</th><th>Estação vista</th><th>Data</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($r['itens'] as $v): ?>
    <tr>
      <td><?= $v['id'] ?></td>
      <td title="<?= e($v['comentario']) ?>"><?= e($v['nome']) ?></td>
      <td><?= e($v['email']) ?></td>
      <td><?= e($v['instituicao'] ?: '—') ?></td>
      <td><?= e($v['cidade'] ?: '—') ?></td>
      <td><?= e($v['estacao_nome'] ?: '—') ?></td>
      <td><?= data_br($v['visitado_em']) ?></td>
      <td class="acoes">
        <a class="btn-sm" href="<?= url('/visitantes/' . $v['id'] . '/editar') ?>">Editar</a>
        <form method="post" action="<?= url('/visitantes/' . $v['id'] . '/excluir') ?>" class="inline"
              onsubmit="return confirm('Excluir este visitante?')">
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
