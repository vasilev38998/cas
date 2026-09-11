<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
$user=current_user();
site_head('CandyClub — игры');
echo '<body class="cc-site">';site_nav($user);
?>
<main>
  <section class="cc-container cc-hero">
    <div>
      <span class="cc-kicker">Новая игровая платформа</span>
      <h1>Играй в <em>Сладкий каскад</em> и новые игры</h1>
      <p>Аккаунт, единый виртуальный баланс, история операций и серверное сохранение прогресса. Начинаем со «Сладкого каскада», а дальше библиотека будет расширяться.</p>
      <div class="cc-hero-actions">
        <?php if($user): ?>
          <a class="cc-btn cc-btn-primary" href="game.php">Играть сейчас</a>
          <a class="cc-btn" href="account.php">Мой аккаунт</a>
        <?php else: ?>
          <a class="cc-btn cc-btn-primary" href="register.php">Создать аккаунт</a>
          <a class="cc-btn" href="login.php">У меня уже есть аккаунт</a>
        <?php endif; ?>
      </div>
    </div>
    <a class="cc-hero-card" href="<?= $user?'game.php':'register.php' ?>" aria-label="Открыть Сладкий каскад"></a>
  </section>

  <section class="cc-section" id="games">
    <div class="cc-container">
      <div class="cc-section-head"><div><h2>Игры</h2><p>Один аккаунт и единый виртуальный баланс для всей библиотеки.</p></div></div>
      <div class="cc-games">
        <a class="cc-game-card" href="<?= $user?'game.php':'login.php' ?>">
          <div class="cc-game-art"></div>
          <div class="cc-game-meta"><strong>Сладкий каскад</strong><small>Кластеры • каскады • множители • фриспины</small></div>
        </a>
        <div class="cc-game-card cc-coming"><div class="cc-game-art" style="background:linear-gradient(135deg,#244f73,#7653c4,#f35c94)"></div><div class="cc-game-meta"><strong>Следующая игра</strong><small>Добавим в общий каталог без изменения аккаунтов.</small></div></div>
        <div class="cc-game-card cc-coming"><div class="cc-game-art" style="background:linear-gradient(135deg,#325843,#9b7130,#e45178)"></div><div class="cc-game-meta"><strong>Ещё одна игра</strong><small>Архитектура уже рассчитана на несколько игр.</small></div></div>
      </div>
    </div>
  </section>
</main>
<?php site_footer(); ?>
