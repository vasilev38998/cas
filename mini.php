<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/mini_games.php';
$user=require_login();
$game=(string)($_GET['game']??'plinko');
$defs=[
 'plinko'=>['title'=>'Плинко Лаб','subtitle'=>'12 рядов • физика шарика • крайние множители','icon'=>'●','rules'=>'Сервер заранее определяет путь шарика через 12 рядов. Центр выпадает чаще, крайние ячейки реже и дают более крупный множитель.'],
 'wheel'=>['title'=>'Колесо импульса','subtitle'=>'10 секторов • плавная остановка • множители','icon'=>'◉','rules'=>'Сектор выбирается сервером по фиксированным весам, после чего интерфейс анимирует колесо до рассчитанной позиции.'],
 'mines'=>['title'=>'Кристальные мины','subtitle'=>'5×5 • 3/5/8/10 мин • забрать в любой момент','icon'=>'◆','rules'=>'Ставка списывается при старте. Открывайте безопасные клетки, множитель растёт после каждой. Можно забрать виртуальный выигрыш в любой момент.']
];
if(!isset($defs[$game]))$game='plinko';$g=$defs[$game];
$activeMines=$game==='mines'?mini_mines_get((int)$user['id']):null;
$boot=['game'=>$game,'balance'=>(int)$user['balance_kopecks']/100,'csrf'=>csrf_token(),'activeMines'=>$activeMines?['bet'=>(int)$activeMines['betK']/100,'mines'=>(int)$activeMines['mines'],'opened'=>$activeMines['revealed'],'multiplier'=>mini_mines_multiplier((int)$activeMines['mines'],count($activeMines['revealed']))]:null];
?><!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#0c0d17"><title><?=e($g['title'])?> — CandyClub</title><link rel="stylesheet" href="<?=asset_url('mini.css')?>"></head>
<body class="mini-page">
<header class="mini-top"><a href="index.php">← Лобби</a><span class="mini-title"><?=e($g['title'])?></span><span class="mini-balance" id="topBalance"><?=e(money_rub((int)$user['balance_kopecks']))?></span><a href="account.php"><?=e($user['username'])?></a></header>
<main class="mini-shell">
 <nav class="mini-nav"><h3>Мини-игры</h3><?php foreach($defs as $k=>$d): ?><a href="mini.php?game=<?=e($k)?>" class="<?=$k===$game?'active':''?>"><i><?=e($d['icon'])?></i><div><strong><?=e($d['title'])?></strong><small><?=e($d['subtitle'])?></small></div></a><?php endforeach; ?></nav>
 <section class="mini-stage">
  <div class="mini-stage-head"><div><h1><?=e($g['title'])?></h1><p><?=e($g['subtitle'])?></p></div><span class="mini-status" id="miniStatus">ГОТОВО</span></div>
  <div class="mini-gamebox" id="miniGameBox"></div>
  <div class="mini-controls">
    <div class="mini-bet"><button id="betMinus">−</button><div><span>Ставка</span><strong id="betValue">100 ₽</strong></div><button id="betPlus">+</button></div>
    <?php if($game==='mines'): ?><div class="mine-options" id="mineOptions"><button data-mines="3">3 мины</button><button data-mines="5" class="active">5 мин</button><button data-mines="8">8 мин</button><button data-mines="10">10 мин</button></div><button class="mini-primary" id="playBtn">НАЧАТЬ</button><button class="mini-secondary" id="cashBtn" disabled>ЗАБРАТЬ</button><?php else: ?><button class="mini-primary" id="playBtn"><?=$game==='wheel'?'КРУТИТЬ КОЛЕСО':'БРОСИТЬ ШАРИК'?></button><?php endif; ?>
  </div>
 </section>
 <aside class="mini-info"><h3>Раунд</h3><div class="mini-stat"><span>Последний результат</span><strong id="lastResult">—</strong></div><div class="mini-stat"><span>Последний выигрыш</span><strong id="lastWin">0 ₽</strong></div><div class="mini-stat"><span>Баланс</span><strong id="sideBalance"><?=e(money_rub((int)$user['balance_kopecks']))?></strong></div><p class="mini-rules"><?=e($g['rules'])?><br><br>Все значения виртуальные. Результат рассчитывается на сервере и записывается в историю аккаунта.</p></aside>
</main>
<script>window.MINI_BOOT=<?=json_encode($boot,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)?>;</script><script src="<?=asset_url('mini-games.js')?>"></script>
</body></html>
