<?php /** $leitura, $erros, $acao, $estacoes */ $v = fn($k) => e($leitura[$k] ?? '');
$err = fn($k) => isset($erros[$k]) ? '<span class="erro">' . e($erros[$k]) . '</span>' : ''; ?>
<h1><?= e($titulo) ?></h1>
<form method="post" action="<?= url($acao) ?>" class="cartao form">
  <?= csrf_field() ?>
  <label>Estação *
    <select name="estacao_id" required>
      <option value="">Selecione…</option>
      <?php foreach ($estacoes as $e): ?>
        <option value="<?= $e['id'] ?>" <?= sel($e['id'], $leitura['estacao_id'] ?? '') ?>><?= e($e['nome']) ?></option>
      <?php endforeach; ?>
    </select><?= $err('estacao_id') ?></label>
  <div class="tres-col">
    <label>Temperatura (°C) *<input name="temperatura" inputmode="decimal" value="<?= $v('temperatura') ?>" required><?= $err('temperatura') ?></label>
    <label>Umidade (%) *<input name="umidade" inputmode="decimal" value="<?= $v('umidade') ?>" required><?= $err('umidade') ?></label>
    <label>Luminosidade (%) *<input name="luminosidade" inputmode="numeric" value="<?= $v('luminosidade') ?>" required><?= $err('luminosidade') ?></label>
  </div>
  <div class="duas-col">
    <label>LDR bruto (0–4095)<input name="ldr_bruto" inputmode="numeric" value="<?= $v('ldr_bruto') ?>"><?= $err('ldr_bruto') ?></label>
    <label>Data/hora (vazio = agora)<input type="datetime-local" name="registrada_em" value="<?= $v('registrada_em') ?>"><?= $err('registrada_em') ?></label>
  </div>
  <div class="botoes">
    <button class="btn" type="submit">Salvar</button>
    <a class="btn btn-sec" href="<?= url('/leituras') ?>">Cancelar</a>
  </div>
</form>
