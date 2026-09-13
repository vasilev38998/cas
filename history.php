<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
require __DIR__.'/app/game_catalog.php';
require __DIR__.'/app/mini_catalog.php';
$user=require_login();$uid=(int)$user['id'];$page=max(1,(int)($_GET['page']??1));$per=40;$offset=($page-1)*$per;
$countQ=db()->prepare('SELECT COUNT(*) FROM game_rounds WHERE user_id=?');$countQ->execute([$uid]);$total=(int)$countQ->fetchColumn();$pages=max(1,(int)ceil($total/$per));if($page>$pages){$page=$pages;$offset=($page-1)*$per;}
$q=db()->prepare("SELECT id,game_key,mode,bet_kopecks,cost_kopecks,win_kopecks,balance_before_kopecks,balance_after_kopecks,created_at FROM game_rounds WHERE user_id=? ORDER BY id DESC LIMIT {$per} OFFSET {$offset}");$q->execute([$uid]);$rounds=$q->fetchAll();$games=game_catalog();$minis=mini_catalog();
function history_game_meta(string $key,array $games,array $minis): array {if(str_starts_with($key,'mini-')){$slug=substr($key,5);$m=$minis[$slug]??null;return[$m['title']??$key,$m['icon']??'•'];}$g=$games[$key]??null;return[$g['title']??$key,$g['icon']??'•'];}
function history_mode(string $mode): string {return ['normal'=>'Обычный','free'=>'Фриспин','buy_bonus'=>'Bonus Buy','buy_super'=>'Super Buy','active'=>'Активная','lost'=>'Проигрыш','cashed'=>'Забрано'][$mode]??$mode;}
site_head('История игр — CandyClub');echo '<body class="cc-site">';site_nav($user);
?>
<main class="cc-container cc-account"><div class="cc-section-head"><div><h1 style="margin:0">История игр</h1><p><?=$total?> серверных раундов • страница <?=$page?> из <?=$pages?></p></div><a class="cc-btn" href="account.php">← Профиль</a></div>
<section class="cc-history"><div style="overflow:auto"><table class="cc-table"><thead><tr><th>Раунд</th><th>Игра / режим</th><th>Ставка</th><th>Стоимость</th><th>Выигрыш</th><th>Баланс после</th><th>Время</th></tr></thead><tbody><?php if(!$rounds): ?><tr><td colspan="7">Игровых раундов пока нет.</td></tr><?php else: foreach($rounds as $r): [$title,$icon]=history_game_meta((string)$r['game_key'],$games,$minis); ?><tr><td><a class="cc-round-link" href="round.php?id=<?=(int)$r['id']?>">#<?=(int)$r['id']?> →</a></td><td><strong><?=e($icon.' '.$title)?></strong><br><small><?=e(history_mode((string)$r['mode']))?></small></td><td><?=e(money_rub((int)$r['bet_kopecks']))?></td><td><?=e(money_rub((int)$r['cost_kopecks']))?></td><td class="<?=$r['win_kopecks']>0?'cc-plus':''?>"><?=e(money_rub((int)$r['win_kopecks']))?></td><td><?=e(money_rub((int)$r['balance_after_kopecks']))?></td><td><?=e(date('d.m.Y H:i:s',strtotime((string)$r['created_at'])))?></td></tr><?php endforeach; endif; ?></tbody></table></div>
<?php if($pages>1): ?><div class="cc-pagination"><?php if($page>1): ?><a class="cc-btn" href="history.php?page=<?=$page-1?>">← Новее</a><?php endif; ?><span><?=$page?> / <?=$pages?></span><?php if($page<$pages): ?><a class="cc-btn" href="history.php?page=<?=$page+1?>">Старее →</a><?php endif; ?></div><?php endif; ?>
</section></main>
<?php site_footer(); ?>
