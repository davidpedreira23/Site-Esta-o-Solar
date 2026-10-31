<div class="linha-topo">
  <h1>Estações</h1>
  <a class="btn" href="<?= url('/estacoes/novo') ?>">+ Nova estação</a>
</div>
<?php if (!$estacoes): ?>
  <section class="vazio"><p>Nenhuma estação cadastrada.</p></section>
<?php else: ?>
<div class="tabela-wrap"><table>
  <thead><tr><th>#</th><th>Nome</th><th>Local</th><th>Status</th><th>Leituras</th><th>Último contato</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($estacoes as $e): ?>
    <tr>
      <td><?= $e['id'] ?></td>
      <td><a href="<?= url('/estacoes/' . $e['id']) ?>"><?= e($e['nome']) ?></a></td>
      <td><?= e($e['local']) ?></td>
      <td>
        <?php if (!$e['ativa']): ?><span class="selo selo-off">desativada</span>
        <?php elseif ($e['online']): ?><span class="selo selo-on">online</span>
        <?php else: ?><span class="selo selo-warn">offline</span><?php endif; ?>
      </td>
      <td><?= (int) $e['total_leituras'] ?></td>
      <td><?= data_br($e['ultimo_contato']) ?></td>
      <td class="acoes">
        <a class="btn-sm" href="<?= url('/estacoes/' . $e['id']) ?>">Ver</a>
        <a class="btn-sm" href="<?= url('/estacoes/' . $e['id'] . '/editar') ?>">Editar</a>
        <form method="post" action="<?= url('/estacoes/' . $e['id'] . '/excluir') ?>" class="inline"
              onsubmit="return confirm('Excluir a estação e TODAS as suas leituras?')">
          <?= csrf_field() ?><button class="btn-sm btn-perigo" type="submit">Excluir</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
