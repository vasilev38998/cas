<?php
declare(strict_types=1);
require_once __DIR__.'/bootstrap.php';

function mini_allowed_bets(): array { return [10,20,50,100,200,500,1000]; }
function mini_validate_bet(int $betRub): int {
    if (!in_array($betRub, mini_allowed_bets(), true)) throw new RuntimeException('Недопустимая ставка.');
    return $betRub * 100;
}
function mini_pick_weighted(array $weights): int {
    $total = array_sum($weights); $r = random_int(1, max(1,$total));
    foreach ($weights as $i=>$w) { $r -= $w; if ($r <= 0) return (int)$i; }
    return (int)array_key_last($weights);
}
function mini_direct_round(PDO $pdo,int $userId,string $game,int $betRub,float $mult,array $result): array {
    $betK = mini_validate_bet($betRub);
    $winK = max(0,(int)round($betK*$mult));
    $pdo->beginTransaction();
    try {
        $q=$pdo->prepare('SELECT balance_kopecks FROM users WHERE id=? FOR UPDATE');$q->execute([$userId]);$u=$q->fetch();
        if(!$u) throw new RuntimeException('Аккаунт не найден.');
        $before=(int)$u['balance_kopecks']; if($before<$betK) throw new RuntimeException('Недостаточно виртуальных средств.');
        $after=$before-$betK+$winK;
        $pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);
        $payload=array_merge($result,['multiplier'=>$mult]);
        $pdo->prepare('INSERT INTO game_rounds(user_id,game_key,mode,bet_kopecks,cost_kopecks,win_kopecks,balance_before_kopecks,balance_after_kopecks,result_json) VALUES(?,?,?,?,?,?,?,?,?)')
            ->execute([$userId,'mini-'.$game,'normal',$betK,$betK,$winK,$before,$after,json_encode($payload,JSON_UNESCAPED_UNICODE)]);
        $round=(int)$pdo->lastInsertId();$ref='round:'.$round;
        wallet_entry($pdo,$userId,'game_bet',-$betK,$before-$betK,$ref,['game'=>'mini-'.$game]);
        if($winK>0) wallet_entry($pdo,$userId,'game_win',$winK,$after,$ref,['game'=>'mini-'.$game,'multiplier'=>$mult]);
        $pdo->commit();
        return ['ok'=>true,'round_id'=>$round,'game'=>$game,'bet'=>$betRub,'multiplier'=>$mult,'win'=>$winK/100,'balance_before'=>$before/100,'balance_after'=>$after/100]+$result;
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); throw $e; }
}
function mini_plinko(PDO $pdo,int $userId,int $betRub): array {
    $rows=12;$right=0;$path=[];
    for($r=0;$r<$rows;$r++){ $dir=random_int(0,1); $right+=$dir; $path[]=$dir; }
    $mults=[8.0,3.0,1.8,1.15,.75,.48,.32,.48,.75,1.15,1.8,3.0,8.0];
    $mult=$mults[$right];
    return mini_direct_round($pdo,$userId,'plinko',$betRub,$mult,['rows'=>$rows,'path'=>$path,'bin'=>$right,'multipliers'=>$mults]);
}
function mini_wheel(PDO $pdo,int $userId,int $betRub): array {
    $segments=[
        ['label'=>'0×','mult'=>0.0],['label'=>'0.4×','mult'=>.4],['label'=>'0.6×','mult'=>.6],['label'=>'0.8×','mult'=>.8],
        ['label'=>'1×','mult'=>1.0],['label'=>'1.2×','mult'=>1.2],['label'=>'1.5×','mult'=>1.5],['label'=>'2×','mult'=>2.0],['label'=>'3×','mult'=>3.0],['label'=>'5×','mult'=>5.0]
    ];
    $idx=mini_pick_weighted([13,18,18,15,12,9,6,4,3,2]);
    return mini_direct_round($pdo,$userId,'wheel',$betRub,(float)$segments[$idx]['mult'],['segment'=>$idx,'segments'=>$segments]);
}
function mini_comb(int $n,int $k): float {
    if($k<0||$k>$n)return 0.0;if($k===0||$k===$n)return 1.0;$k=min($k,$n-$k);$v=1.0;
    for($i=1;$i<=$k;$i++)$v=$v*($n-$k+$i)/$i; return $v;
}
function mini_mines_multiplier(int $mines,int $safeOpened): float {
    if($safeOpened<=0)return 1.0;
    $safe=25-$mines;if($safeOpened>$safe)return 0.0;
    $prob=mini_comb($safe,$safeOpened)/mini_comb(25,$safeOpened);
    if($prob<=0)return 0.0;
    return round(max(1.01,.96/$prob),2);
}
function mini_mines_start(PDO $pdo,int $userId,int $betRub,int $mines): array {
    $betK=mini_validate_bet($betRub); if(!in_array($mines,[3,5,8,10],true))throw new RuntimeException('Недопустимое число мин.');
    $pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT balance_kopecks FROM users WHERE id=? FOR UPDATE');$q->execute([$userId]);$u=$q->fetch();if(!$u)throw new RuntimeException('Аккаунт не найден.');
        $before=(int)$u['balance_kopecks'];if($before<$betK)throw new RuntimeException('Недостаточно виртуальных средств.');$after=$before-$betK;
        $pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);
        $pdo->prepare('INSERT INTO game_rounds(user_id,game_key,mode,bet_kopecks,cost_kopecks,win_kopecks,balance_before_kopecks,balance_after_kopecks,result_json) VALUES(?,?,?,?,?,?,?,?,?)')
            ->execute([$userId,'mini-mines','active',$betK,$betK,0,$before,$after,json_encode(['mines'=>$mines,'opened'=>0],JSON_UNESCAPED_UNICODE)]);
        $round=(int)$pdo->lastInsertId();wallet_entry($pdo,$userId,'game_bet',-$betK,$after,'round:'.$round,['game'=>'mini-mines','mines'=>$mines]);
        $board=array_fill(0,25,false);$pos=range(0,24);for($i=24;$i>0;$i--){$j=random_int(0,$i);[$pos[$i],$pos[$j]]=[$pos[$j],$pos[$i]];}
        foreach(array_slice($pos,0,$mines) as $p)$board[$p]=true;
        $_SESSION['cc_mines_'.$userId]=['round'=>$round,'board'=>$board,'revealed'=>[],'betK'=>$betK,'mines'=>$mines,'balance_after_bet'=>$after,'started'=>time()];
        $pdo->commit();
        return ['ok'=>true,'game'=>'mines','state'=>'active','round_id'=>$round,'bet'=>$betRub,'mines'=>$mines,'balance_after'=>$after/100,'multiplier'=>1.0,'opened'=>[]];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function mini_mines_get(int $userId): ?array { $s=$_SESSION['cc_mines_'.$userId]??null; return is_array($s)?$s:null; }
function mini_mines_reveal(PDO $pdo,int $userId,int $cell): array {
    $s=mini_mines_get($userId);if(!$s)throw new RuntimeException('Активная игра не найдена.');if($cell<0||$cell>24)throw new RuntimeException('Некорректная клетка.');
    if(in_array($cell,$s['revealed'],true))throw new RuntimeException('Клетка уже открыта.');
    if(!empty($s['board'][$cell])){
        $mineCells=[];foreach($s['board'] as $i=>$v)if($v)$mineCells[]=$i;
        $pdo->prepare('UPDATE game_rounds SET mode=?,result_json=? WHERE id=? AND user_id=?')->execute(['lost',json_encode(['mines'=>$s['mines'],'opened'=>count($s['revealed']),'hit'=>$cell],JSON_UNESCAPED_UNICODE),(int)$s['round'],$userId]);
        unset($_SESSION['cc_mines_'.$userId]);
        return ['ok'=>true,'state'=>'lost','cell'=>$cell,'mine_cells'=>$mineCells,'multiplier'=>0,'win'=>0];
    }
    $s['revealed'][]=$cell;$_SESSION['cc_mines_'.$userId]=$s;$opened=count($s['revealed']);$mult=mini_mines_multiplier((int)$s['mines'],$opened);
    $safe=25-(int)$s['mines'];
    if($opened>=$safe) return mini_mines_cashout($pdo,$userId,true);
    return ['ok'=>true,'state'=>'active','cell'=>$cell,'opened'=>$s['revealed'],'multiplier'=>$mult,'potential_win'=>round($s['betK']*$mult/100,2)];
}
function mini_mines_cashout(PDO $pdo,int $userId,bool $auto=false): array {
    $s=mini_mines_get($userId);if(!$s)throw new RuntimeException('Активная игра не найдена.');$opened=count($s['revealed']);if($opened<=0)throw new RuntimeException('Откройте хотя бы одну безопасную клетку.');
    $mult=mini_mines_multiplier((int)$s['mines'],$opened);$winK=(int)round((int)$s['betK']*$mult);
    $pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT balance_kopecks FROM users WHERE id=? FOR UPDATE');$q->execute([$userId]);$u=$q->fetch();if(!$u)throw new RuntimeException('Аккаунт не найден.');$before=(int)$u['balance_kopecks'];$after=$before+$winK;
        $pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);
        $pdo->prepare('UPDATE game_rounds SET mode=?,win_kopecks=?,balance_after_kopecks=?,result_json=? WHERE id=? AND user_id=?')->execute(['cashed',$winK,$after,json_encode(['mines'=>$s['mines'],'opened'=>$opened,'multiplier'=>$mult,'auto'=>$auto],JSON_UNESCAPED_UNICODE),(int)$s['round'],$userId]);
        wallet_entry($pdo,$userId,'game_win',$winK,$after,'round:'.$s['round'],['game'=>'mini-mines','multiplier'=>$mult]);
        $mineCells=[];foreach($s['board'] as $i=>$v)if($v)$mineCells[]=$i;
        $pdo->commit();unset($_SESSION['cc_mines_'.$userId]);
        return ['ok'=>true,'state'=>'cashed','multiplier'=>$mult,'win'=>$winK/100,'balance_after'=>$after/100,'mine_cells'=>$mineCells,'opened'=>$s['revealed']];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
function daily_reward_status(PDO $pdo,int $userId): array {
    $q=$pdo->prepare("SELECT created_at FROM wallet_transactions WHERE user_id=? AND type='daily_bonus' ORDER BY id DESC LIMIT 1");$q->execute([$userId]);$last=$q->fetchColumn();
    $available=!$last || date('Y-m-d',strtotime((string)$last))<date('Y-m-d');
    return ['available'=>$available,'last'=>$last?:null];
}
function daily_reward_claim(PDO $pdo,int $userId): array {
    $pdo->beginTransaction();
    try{
        $q=$pdo->prepare('SELECT balance_kopecks FROM users WHERE id=? FOR UPDATE');$q->execute([$userId]);$u=$q->fetch();if(!$u)throw new RuntimeException('Аккаунт не найден.');
        $q=$pdo->prepare("SELECT created_at FROM wallet_transactions WHERE user_id=? AND type='daily_bonus' ORDER BY id DESC LIMIT 1 FOR UPDATE");$q->execute([$userId]);$last=$q->fetchColumn();
        if($last && date('Y-m-d',strtotime((string)$last))>=date('Y-m-d'))throw new RuntimeException('Ежедневный подарок уже получен сегодня.');
        $pool=[5000,7500,10000,12500,15000,20000,25000];$bonus=$pool[array_rand($pool)];$before=(int)$u['balance_kopecks'];$after=$before+$bonus;
        $pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);wallet_entry($pdo,$userId,'daily_bonus',$bonus,$after,'daily:'.date('Y-m-d'),['source'=>'daily_gift']);$pdo->commit();
        return ['ok'=>true,'bonus'=>$bonus/100,'balance_after'=>$after/100];
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
