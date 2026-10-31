<?php /** $visitante, $erros, $acao, $estacoes, $publico */ $v = fn($k) => e($visitante[$k] ?? '');
$err = fn($k) => isset($erros[$k]) ? '<span class="erro">' . e($erros[$k]) . '</span>' : ''; ?>
<h1><?= e($titulo) ?></h1>
<?php if ($publico): ?><p class="mudo">Deixe seu registro na nossa Feira Tecnológica. É rapidinho! 🙂</p><?php endif; ?>
<form method="post" action="<?= url($acao) ?>" class="cartao form">
  <?= csrf_field() ?>
  <label>Nome *<input name="nome" maxlength="120" value="<?= $v('nome') ?>" required><?= $err('nome') ?></label>
  <label>E-mail *<input type="email" name="email" maxlength="150" value="<?= $v('email') ?>" required><?= $err('email') ?></label>
  <div class="duas-col">
    <label>Escola / Empresa<input name="instituicao" maxlength="150" value="<?= $v('instituicao') ?>"><?= $err('instituicao') ?></label>
    <label>Cidade<input name="cidade" maxlength="100" value="<?= $v('cidade') ?>"><?= $err('cidade') ?></label>
  </div>
  <label>Qual estação você viu?
    <select name="estacao_id">
      <option value="">Nenhuma / não sei</option>
      <?php foreach ($estacoes as $e): ?>
        <option value="<?= $e['id'] ?>" <?= sel($e['id'], $visitante['estacao_id'] ?? '') ?>><?= e($e['nome']) ?></option>
      <?php endforeach; ?>
    </select><?= $err('estacao_id') ?></label>
  <label>Comentário<textarea name="comentario" rows="3" maxlength="500"><?= $v('comentario') ?></textarea><?= $err('comentario') ?></label>
  <div class="botoes">
    <button class="btn" type="submit">Enviar</button>
    <a class="btn btn-sec" href="<?= url($publico ? '/' : '/visitantes') ?>">Cancelar</a>
  </div>
</form>
