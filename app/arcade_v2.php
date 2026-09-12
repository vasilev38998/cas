<?php
declare(strict_types=1);
require_once __DIR__.'/high_volatility_engines.php';
require_once __DIR__.'/precision_engines.php';
require_once __DIR__.'/sky_pantheon.php';

function arcade_game_spin_hv(PDO $pdo,int $userId,string $gameKey,int $betRub): array {
    $allowed=[10,20,50,100,200,500,1000];
    if(!in_array($betRub,$allowed,true))throw new RuntimeException('Недопустимая ставка.');
    $engines=[
        'fruit-fiesta'=>'hv_fruit',
        'temple-ways'=>'hv3_temple',
        'jungle-hold'=>'hv_jungle',
        'crystal-clusters'=>'hv_crystal',
        'sun-scroll'=>'hv_scroll',
        'neon-rush'=>'hv3_neon',
        'sky-pantheon'=>'hv_sky'
    ];
    if(!isset($engines[$gameKey]))throw new RuntimeException('Игра не найдена.');
    $betK=$betRub*100;$pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT id,balance_kopecks FROM users WHERE id=? FOR UPDATE');$q->execute([$userId]);$u=$q->fetch();if(!$u)throw new RuntimeException('Аккаунт не найден.');$before=(int)$u['balance_kopecks'];
        $q=$pdo->prepare('SELECT free_spins,multiplier_map_json FROM user_game_states WHERE user_id=? AND game_key=? FOR UPDATE');$q->execute([$userId,$gameKey]);$gs=$q->fetch();
        if(!$gs){$pdo->prepare('INSERT INTO user_game_states(user_id,game_key,multiplier_map_json) VALUES(?,?,?)')->execute([$userId,$gameKey,'{}']);$gs=['free_spins'=>0,'multiplier_map_json'=>'{}'];}
        $free=(int)$gs['free_spins'];$state=json_decode((string)$gs['multiplier_map_json'],true);if(!is_array($state))$state=[];$isFree=$free>0;if($isFree)$free--;$cost=$isFree?0:$betK;if($before<$cost)throw new RuntimeException('Недостаточно виртуальных средств.');
        $engine=$engines[$gameKey];$result=$engine($betK,$isFree,$free,$state);$win=max(0,(int)$result['win']);$cap=$betK*10000;if($win>$cap)$win=$cap;$after=$before-$cost+$win;
        $pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);
        $pdo->prepare('UPDATE user_game_states SET free_spins=?,storm_charge=0,multiplier_map_json=? WHERE user_id=? AND game_key=?')->execute([$free,json_encode($state,JSON_UNESCAPED_UNICODE),$userId,$gameKey]);
        $summary=['free'=>$isFree,'free_spins_after'=>$free,'feature'=>!empty($result['payload']['feature']),'math_profile'=>'high-volatility-v3'];
        $pdo->prepare('INSERT INTO game_rounds(user_id,game_key,mode,bet_kopecks,cost_kopecks,win_kopecks,balance_before_kopecks,balance_after_kopecks,result_json) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$userId,$gameKey,$isFree?'free':'normal',$betK,$cost,$win,$before,$after,json_encode($summary,JSON_UNESCAPED_UNICODE)]);
        $round=(int)$pdo->lastInsertId();$ref='round:'.$round;
        if($cost>0)wallet_entry($pdo,$userId,'game_bet',-$cost,$before-$cost,$ref,['game'=>$gameKey,'bet'=>$betRub]);
        if($win>0)wallet_entry($pdo,$userId,'game_win',$win,$after,$ref,['game'=>$gameKey]);
        $pdo->commit();
        return array_merge(['ok'=>true,'round_id'=>$round,'game'=>$gameKey,'bet'=>$betRub,'is_free_spin'=>$isFree,'cost'=>$cost/100,'balance_before'=>$before/100,'balance_after'=>$after/100,'total_win'=>$win/100,'free_spins'=>$free,'math_profile'=>'high-volatility-v3'],$result['payload']);
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
