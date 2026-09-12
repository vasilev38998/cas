<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/arcade_v2.php';
require dirname(__DIR__).'/app/sweet_cascade.php';
require dirname(__DIR__).'/app/mini_games.php';

function smoke_assert(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
$pdo=db();$name='ci_'.bin2hex(random_bytes(4));$email=$name.'@example.test';$uid=0;
try{
    $start=50000000;$q=$pdo->prepare('INSERT INTO users(username,email,password_hash,balance_kopecks) VALUES(?,?,?,?)');$q->execute([$name,$email,password_hash('Integration123',PASSWORD_DEFAULT),$start]);$uid=(int)$pdo->lastInsertId();wallet_entry($pdo,$uid,'welcome_bonus',$start,$start,'ci-signup');
    echo "user {$uid} created\n";

    $slot=arcade_game_spin_hv($pdo,$uid,'fruit-fiesta',100,'normal');smoke_assert(!empty($slot['round_id'])&&isset($slot['balance_after']),'slot failed');echo "slot OK #{$slot['round_id']}\n";
    $sweet=sweet_cascade_spin($pdo,$uid,100,'normal');smoke_assert(!empty($sweet['round_id'])&&isset($sweet['final_grid']),'sweet failed');echo "sweet OK #{$sweet['round_id']}\n";
    $plinko=mini_plinko($pdo,$uid,100);smoke_assert(count($plinko['path'])===12&&isset($plinko['bin']),'plinko failed');echo "plinko OK\n";
    $wheel=mini_wheel($pdo,$uid,100);smoke_assert(isset($wheel['segment'],$wheel['multiplier']),'wheel failed');echo "wheel OK\n";
    $crash=mini_crash($pdo,$uid,100,2.0);smoke_assert(isset($crash['crash_point'],$crash['success'])&&$crash['crash_point']>=1,'crash failed');echo "crash OK @ {$crash['crash_point']}x\n";

    $mines=mini_mines_start($pdo,$uid,100,5);smoke_assert(($mines['state']??'')==='active','mines start failed');$state=mini_mines_get($uid);smoke_assert(is_array($state)&&count($state['board'])===25,'mines state failed');$safe=null;foreach($state['board'] as $i=>$mine){if(!$mine){$safe=(int)$i;break;}}smoke_assert($safe!==null,'no safe cell');$reveal=mini_mines_reveal($pdo,$uid,$safe);smoke_assert(in_array($reveal['state'],['active','cashed'],true),'mines reveal failed');if($reveal['state']==='active'){$cash=mini_mines_cashout($pdo,$uid);smoke_assert(($cash['state']??'')==='cashed'&&$cash['win']>0,'mines cashout failed');}echo "mines OK\n";

    $daily=daily_reward_claim($pdo,$uid);smoke_assert($daily['bonus']>0,'daily gift failed');$status=daily_reward_status($pdo,$uid);smoke_assert($status['available']===false,'daily duplicate guard failed');echo "daily OK\n";
    $favs=cc_toggle_favorite($uid,'fruit-fiesta');smoke_assert(in_array('fruit-fiesta',$favs,true),'favorite add failed');$favs=cc_toggle_favorite($uid,'fruit-fiesta');smoke_assert(!in_array('fruit-fiesta',$favs,true),'favorite remove failed');echo "favorites OK\n";
    $ctrl=cc_set_controls($uid,100,15,0);smoke_assert($ctrl['max_bet_rub']===100,'control save failed');$blocked=false;try{sweet_cascade_spin($pdo,$uid,200,'normal');}catch(RuntimeException $e){$blocked=str_contains($e->getMessage(),'личного лимита');}smoke_assert($blocked,'stake cap was not enforced');cc_set_controls($uid,0,60,0);echo "play controls OK\n";

    $integrity=$pdo->prepare('SELECT COUNT(*) FROM wallet_transactions WHERE user_id=?');$integrity->execute([$uid]);smoke_assert((int)$integrity->fetchColumn()>=8,'wallet journal unexpectedly short');$r=$pdo->prepare('SELECT COUNT(*) FROM game_rounds WHERE user_id=?');$r->execute([$uid]);smoke_assert((int)$r->fetchColumn()>=6,'round journal unexpectedly short');echo "journals OK\n";
    echo "INTEGRATION_SMOKE_OK\n";
}finally{
    if($uid>0){$pdo->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);echo "cleanup OK\n";}
}
