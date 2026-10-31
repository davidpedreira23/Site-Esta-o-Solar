<?php /** $estacao */
$urlApi = (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . url('/api/leituras'); ?>
<div class="linha-topo">
  <h1><?= e($estacao['nome']) ?></h1>
  <div>
    <a class="btn btn-sec" href="<?= url('/estacoes/' . $estacao['id'] . '/editar') ?>">Editar</a>
    <a class="btn btn-sec" href="<?= url('/leituras?estacao=' . $estacao['id']) ?>">Ver leituras</a>
  </div>
</div>
<section class="cartao">
  <dl class="detalhes">
    <dt>Local</dt><dd><?= e($estacao['local']) ?></dd>
    <dt>Descrição</dt><dd><?= e($estacao['descricao'] ?: '—') ?></dd>
    <dt>Coordenadas</dt><dd><?= $estacao['latitude'] !== null ? e($estacao['latitude'] . ', ' . $estacao['longitude']) : '—' ?></dd>
    <dt>Status</dt><dd><?= !$estacao['ativa'] ? 'Desativada' : ($estacao['online'] ? 'Online' : 'Offline') ?></dd>
    <dt>Último contato</dt><dd><?= data_br($estacao['ultimo_contato'], true) ?></dd>
  </dl>
</section>

<section class="cartao">
  <h2>🔑 Configuração do ESP32</h2>
  <p>Copie estes valores para o arquivo <code>firmware/estacao_solar/config.h</code> (troque o IP do servidor pelo IP do seu PC na rede Wi-Fi):</p>
  <pre>SERVER_URL = "<?= e($urlApi) ?>"
API_KEY    = "<?= e($estacao['api_key']) ?>"</pre>
  <form method="post" action="<?= url('/estacoes/' . $estacao['id'] . '/nova-chave') ?>"
        onsubmit="return confirm('A chave atual deixará de funcionar. Continuar?')">
    <?= csrf_field() ?><button class="btn-sm btn-perigo" type="submit">Gerar nova chave</button></form>
</section>
