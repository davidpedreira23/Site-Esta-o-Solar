<?php /** Layout padrão: menu + área de conteúdo + rodapé. Variáveis: $conteudo, $titulo */
use App\Core\Auth;
$flash = flash();
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo ?? 'Estação Solar') ?> · Estação Solar IoT</title>
  <link rel="stylesheet" href="<?= url('/assets/css/style.css') ?>">
</head>
<body>
<header class="topo">
  <div class="container barra">
    <a class="logo" href="<?= url('/') ?>">☀️ Estação Solar <small>IoT</small></a>
    <nav>
      <a href="<?= url('/') ?>">Painel ao vivo</a>
      <a href="<?= url('/visitantes/novo') ?>">Sou visitante</a>
      <?php if (Auth::check()): ?>
        <a href="<?= url('/estacoes') ?>">Estações</a>
        <a href="<?= url('/leituras') ?>">Leituras</a>
        <a href="<?= url('/visitantes') ?>">Visitantes</a>
        <form method="post" action="<?= url('/logout') ?>" class="inline"><?= csrf_field() ?>
          <button class="link-btn" type="submit">Sair (<?= e(Auth::user()['nome']) ?>)</button></form>
      <?php else: ?>
        <a class="btn-nav" href="<?= url('/login') ?>">Admin</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">
  <?php if ($flash): ?>
    <div class="alerta alerta-<?= e($flash['tipo']) ?>"><?= e($flash['msg']) ?></div>
  <?php endif; ?>
  <?= $conteudo ?>
</main>

<footer class="rodape">
  <div class="container">Feira Tecnológica · ESP32 + DHT22 + LDR + painel solar · PHP + MySQL</div>
</footer>
</body>
</html>
