<?php /** $estacao, $erros, $acao */ $v = fn($k) => e($estacao[$k] ?? '');
$err = fn($k) => isset($erros[$k]) ? '<span class="erro">' . e($erros[$k]) . '</span>' : ''; ?>
<h1><?= e($titulo) ?></h1>
<form method="post" action="<?= url($acao) ?>" class="cartao form">
  <?= csrf_field() ?>
  <label>Nome *<input name="nome" maxlength="100" value="<?= $v('nome') ?>" required><?= $err('nome') ?></label>
  <label>Local *<input name="local" maxlength="150" value="<?= $v('local') ?>" required><?= $err('local') ?></label>
  <label>Descrição<textarea name="descricao" rows="3" maxlength="1000"><?= $v('descricao') ?></textarea><?= $err('descricao') ?></label>
  <div class="duas-col">
    <label>Latitude<input name="latitude" inputmode="decimal" placeholder="-23.550520" value="<?= $v('latitude') ?>"><?= $err('latitude') ?></label>
    <label>Longitude<input name="longitude" inputmode="decimal" placeholder="-46.633308" value="<?= $v('longitude') ?>"><?= $err('longitude') ?></label>
  </div>
  <label class="check"><input type="checkbox" name="ativa" value="1" <?= !empty($estacao['ativa']) ? 'checked' : '' ?>> Estação ativa (aceita leituras do ESP32)</label>
  <div class="botoes">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn btn-sec" href="<?= url('/estacoes') ?>">Cancelar</a>
  </div>
</form>
