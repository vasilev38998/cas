<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/arcade_v2.php';
require dirname(__DIR__).'/app/mini_expansion.php';
require dirname(__DIR__).'/app/privacy.php';

function expansion_assert(bool $ok,string $message): void {if(!$ok)throw new RuntimeException($message);}
$pdo=db();$name='exp_'.bin2hex(random_bytes(4));$email=$name.'@example.test';$uid=0;
try{
    $start=50000000;$q=$pdo->prepare('INSERT INTO users(username,email,password_hash,balance_kopecks) VALUES(?,?,?,?)');$q->execute([$name,$email,password_hash('Expansion123',PASSWORD_DEFAULT),$start]);$uid=(int)$pdo->lastInsertId();wallet_entry($pdo,$uid,'welcome_bonus',$start,$start,'ci-expansion');
    foreach(['forge-tempest','lunar-beasts','clockwork-shift'] as $game){$r=arcade_game_spin_hv($pdo,$uid,$game,100,'normal');expansion_assert(!empty($r['round_id'])&&isset($r['initial_grid'],$r['balance_after']),$game.' transaction failed');echo $game." OK\n";}
    $buy=arcade_game_spin_hv($pdo,$uid,'forge-tempest',20,'buy_bonus');expansion_assert(($buy['mode']??'')==='buy_bonus'&&($buy['free_spins']??0)>0,'forge bonus buy failed');$guard=0;while(($buy['free_spins']??0)>0&&$guard++<80)$buy=arcade_game_spin_hv($pdo,$uid,'forge-tempest',20,'normal');expansion_assert($guard<80,'free-spin chain exceeded test bound');echo "expansion bonus OK\n";

    $dice=mini_dice($pdo,$uid,100,35);expansion_assert(isset($dice['roll'],$dice['success'])&&$dice['roll']>=0&&$dice['roll']<100,'dice failed');echo "dice OK\n";
    $requestId='ci_'.bin2hex(random_bytes(8));$before=(int)$pdo->query('SELECT COUNT(*) FROM game_rounds WHERE user_id='.(int)$uid)->fetchColumn();$first=cc_idempotent_execute($uid,'ci:dice',$requestId,fn()=>mini_dice($pdo,$uid,50,50));$second=cc_idempotent_execute($uid,'ci:dice',$requestId,fn()=>['should_not_execute'=>true]);$after=(int)$pdo->query('SELECT COUNT(*) FROM game_rounds WHERE user_id='.(int)$uid)->fetchColumn();expansion_assert(($first['round_id']??0)===($second['round_id']??-1),'persistent idempotency returned a different round');expansion_assert($after===$before+1,'persistent idempotency created duplicate rounds');expansion_assert(empty($second['should_not_execute']),'persistent idempotency executed duplicate closure');$iq=$pdo->prepare('SELECT COUNT(*) FROM request_idempotency WHERE user_id=? AND scope=? AND request_id=?');$iq->execute([$uid,'ci:dice',$requestId]);expansion_assert((int)$iq->fetchColumn()===1,'persistent idempotency row missing');echo "persistent idempotency OK\n";

    $towerSafe=false;for($attempt=0;$attempt<12&&!$towerSafe;$attempt++){$startTower=mini_tower_start($pdo,$uid,50);expansion_assert(($startTower['state']??'')==='active','tower start failed');$saved=mini_tower_get($uid);expansion_assert(is_array($saved)&&($saved['level']??-1)===0,'tower persistence failed');$step=mini_tower_reveal($pdo,$uid,0);if(($step['state']??'')==='active'){$towerSafe=true;$saved=mini_tower_get($uid);expansion_assert(is_array($saved)&&($saved['level']??0)===1,'tower level persistence failed');$sq=$pdo->prepare('SELECT game_key,free_spins,storm_charge,multiplier_map_json,updated_at FROM user_game_states WHERE user_id=? AND game_key=?');$sq->execute([$uid,'mini-tower']);$safe=cc_sanitize_game_state_for_export($sq->fetch()?:[]);$decoded=json_decode((string)($safe['multiplier_map_json']??''),true);expansion_assert(is_array($decoded)&&isset($decoded['history'])&&!isset($decoded['future_traps']),'tower export sanitizer failed');$cash=mini_tower_cashout($pdo,$uid);expansion_assert(($cash['state']??'')==='cashed'&&$cash['win']>0,'tower cashout failed');}}expansion_assert($towerSafe,'tower did not produce safe first floor within test bound');echo "tower OK\n";

    $daily=daily_reward_claim_v2($pdo,$uid);expansion_assert((float)$daily['bonus']===75.0&&$daily['day']===1,'daily streak day one failed');$status=daily_reward_status_v2($pdo,$uid);expansion_assert($status['available']===false&&$status['streak']>=1,'daily streak status failed');echo "daily streak OK\n";
    echo "EXPANSION_SMOKE_OK\n";
}finally{if($uid>0){$pdo->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);echo "cleanup OK\n";}}
