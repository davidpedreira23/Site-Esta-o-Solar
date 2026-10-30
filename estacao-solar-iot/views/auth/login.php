<section class="cartao estreito">
  <h1>Entrar no painel</h1>
  <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>
  <form method="post" action="<?= url('/login') ?>">
    <?= csrf_field() ?>
    <label>E-mail
      <input type="email" name="email" value="<?= e($email ?? '') ?>" required autofocus></label>
    <label>Senha
      <input type="password" name="senha" required></label>
    <button class="btn" type="submit">Entrar</button>
  </form>
  <p class="dica">Demonstração: <code>admin@feira.com</code> / <code>admin123</code></p>
</section>
