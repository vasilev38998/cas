<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
require __DIR__.'/app/game_catalog.php';
$user=current_user();$games=game_catalog();
site_head('CandyClub — игры');
echo '<body class="cc-site">';site_nav($user);
?>
<main>
  <section class="cc-container cc-hero">
    <div>
      <span class="cc-kicker">8 оригинальных игр • единый аккаунт</span>
      <h1>Один баланс. <em>Восемь разных механик.</em></h1>
      <p>Высокая волатильность, каскады, линии, ways, липкие вайлды, hold-and-respin, расширяющиеся символы, кристальные бомбы, неоновый tumble и небесные сферы-множители. Каждая игра рассчитывается сервером и сохраняет результат в истории аккаунта.</p>
      <div class="cc-hero-actions">
        <?php if($user): ?><a class="cc-btn cc-btn-primary" href="game.php">Играть в Сладкий каскад</a><a class="cc-btn" href="#games">Выбрать другую игру</a>
        <?php else: ?><a class="cc-btn cc-btn-primary" href="register.php">Создать аккаунт</a><a class="cc-btn" href="login.php">У меня уже есть аккаунт</a><?php endif; ?>
      </div>
    </div>
    <a class="cc-hero-card" href="<?=$user?'game.php':'register.php'?>" aria-label="Открыть Сладкий каскад"></a>
  </section>

  <section class="cc-section" id="games"><div class="cc-container"><div class="cc-section-head"><div><h2>Библиотека игр</h2><p>Все игры используют общий виртуальный рублёвый баланс. Профиль волатильности одинаков для всех игроков.</p></div></div><div class="cc-games cc-games-library">
  <?php foreach($games as $slug=>$g): $href=$user?$g['route']:'login.php'; ?>
    <a class="cc-game-card" href="<?=e($href)?>"><div class="cc-game-art cc-dynamic-art" style="background:<?=e($g['accent'])?>"><span class="cc-game-icon"><?=e($g['icon'])?></span><span class="cc-game-shine"></span></div><div class="cc-game-meta"><strong><?=e($g['title'])?></strong><small><?=e($g['subtitle'])?></small></div></a>
  <?php endforeach; ?>
  </div></div></section>
</main>
<?php site_footer(); ?>
