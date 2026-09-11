<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
$user=require_login();
$stmt=db()->prepare('SELECT type,amount_kopecks,balance_after_kopecks,reference,created_at FROM wallet_transactions WHERE user_id=? ORDER BY id DESC LIMIT 50');
$stmt->execute([(int)$user['id']]);$rows=$stmt->fetchAll();
$labels=['welcome_bonus'=>'Стартовый баланс','game_bet'=>'Ставка','feature_purchase'=>'Покупка бонуса','game_win'=>'Выигрыш'];
site_head('Аккаунт — CandyClub');echo '<body class="cc-site">';site_nav($user);
?>
<main class="cc-container cc-account">
  <div class="cc-account-grid">
    <aside class="cc-profile-card">
      <div class="cc-avatar"><?=e(mb_strtoupper(mb_substr($user['username'],0,1)))?></div>
      <h2><?=e($user['username'])?></h2><p><?=e($user['email'])?></p>
      <div class="cc-big-balance"><small>Виртуальный игровой баланс</small><strong><?=e(money_rub((int)$user['balance_kopecks']))?></strong></div>
      <a class="cc-btn cc-btn-primary" style="width:100%;margin-top:14px" href="game.php">Играть в Сладкий каскад</a>
      <form action="logout.php" method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><button class="cc-btn" style="width:100%;margin-top:9px" type="submit">Выйти из аккаунта</button></form>
      <p style="font-size:11px;margin-top:16px">Баланс предназначен только для игры внутри платформы и не является денежным счётом.</p>
    </aside>
    <section class="cc-history">
      <h2>История баланса</h2>
      <?php if(!$rows): ?><div class="cc-empty">Операций пока нет.</div><?php else: ?>
      <table class="cc-table"><thead><tr><th>Операция</th><th>Сумма</th><th>Баланс после</th><th>Дата</th></tr></thead><tbody>
      <?php foreach($rows as $r): $amount=(int)$r['amount_kopecks']; ?>
        <tr><td><?=e($labels[$r['type']]??$r['type'])?></td><td class="<?=$amount>=0?'cc-plus':'cc-minus'?>"><?=$amount>=0?'+':''?><?=e(money_rub($amount))?></td><td><?=e(money_rub((int)$r['balance_after_kopecks']))?></td><td><?=e(date('d.m.Y H:i',strtotime($r['created_at'])))?></td></tr>
      <?php endforeach; ?>
      </tbody></table><?php endif; ?>
    </section>
  </div>
</main>
<?php site_footer(); ?>
