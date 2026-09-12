<?php
declare(strict_types=1);

const CC_PROFILE_STATE_KEY='__profile__';

function cc_profile_defaults(): array {
    return ['favorites'=>[],'controls'=>['max_bet_rub'=>0,'session_reminder_minutes'=>60,'pause_until'=>0],'adult_confirmed_at'=>null];
}
function cc_profile_state(int $userId,bool $forUpdate=false,?PDO $pdo=null): array {
    $pdo=$pdo?:db();$sql='SELECT multiplier_map_json FROM user_game_states WHERE user_id=? AND game_key=? LIMIT 1'.($forUpdate?' FOR UPDATE':'');
    $q=$pdo->prepare($sql);$q->execute([$userId,CC_PROFILE_STATE_KEY]);$raw=$q->fetchColumn();$state=$raw?json_decode((string)$raw,true):[];if(!is_array($state))$state=[];$state=array_replace_recursive(cc_profile_defaults(),$state);
    if(!is_array($state['favorites']))$state['favorites']=[];$state['favorites']=array_values(array_unique(array_filter(array_map('strval',$state['favorites']))));return$state;
}
function cc_profile_state_save(PDO $pdo,int $userId,array $state): void {
    $json=json_encode($state,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$pdo->prepare("INSERT INTO user_game_states(user_id,game_key,free_spins,storm_charge,multiplier_map_json) VALUES(?,?,0,0,?) ON DUPLICATE KEY UPDATE multiplier_map_json=VALUES(multiplier_map_json),updated_at=CURRENT_TIMESTAMP")->execute([$userId,CC_PROFILE_STATE_KEY,$json]);
}
function cc_mark_adult_confirmed(PDO $pdo,int $userId): void {$state=cc_profile_state($userId,false,$pdo);$state['adult_confirmed_at']=gmdate('c');cc_profile_state_save($pdo,$userId,$state);}
function cc_controls(int $userId): array {
    $state=cc_profile_state($userId);$c=$state['controls']??[];$allowedReminders=[0,15,30,60,120];$allowedCaps=[0,10,20,50,100,200,500,1000,2000];$cap=(int)($c['max_bet_rub']??0);if(!in_array($cap,$allowedCaps,true))$cap=0;$rem=(int)($c['session_reminder_minutes']??60);if(!in_array($rem,$allowedReminders,true))$rem=60;return['max_bet_rub'=>$cap,'session_reminder_minutes'=>$rem,'pause_until'=>max(0,(int)($c['pause_until']??0))];
}
function cc_set_controls(int $userId,int $maxBet,int $reminder,int $pauseHours=0): array {
    $allowedCaps=[0,10,20,50,100,200,500,1000,2000];$allowedReminders=[0,15,30,60,120];$allowedPause=[0,1,24,168];if(!in_array($maxBet,$allowedCaps,true)||!in_array($reminder,$allowedReminders,true)||!in_array($pauseHours,$allowedPause,true))throw new RuntimeException('Некорректные параметры игровых ограничений.');
    $pdo=db();$pdo->beginTransaction();try{$state=cc_profile_state($userId,true,$pdo);$current=max(0,(int)($state['controls']['pause_until']??0));$newPause=$current;if($pauseHours>0)$newPause=max($current,time()+$pauseHours*3600);$state['controls']=['max_bet_rub'=>$maxBet,'session_reminder_minutes'=>$reminder,'pause_until'=>$newPause];cc_profile_state_save($pdo,$userId,$state);$pdo->commit();return$state['controls'];}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw$e;}
}
function cc_play_guard(int $userId,?int $betRub=null,bool $enforceStake=true): array {
    $c=cc_controls($userId);if($c['pause_until']>time())throw new RuntimeException('Игровая пауза активна до '.date('d.m.Y H:i',$c['pause_until']).'.');if($enforceStake&&$betRub!==null&&$c['max_bet_rub']>0&&$betRub>$c['max_bet_rub'])throw new RuntimeException('Ставка выше вашего личного лимита '.$c['max_bet_rub'].' ₽.');return$c;
}
function cc_toggle_favorite(int $userId,string $key): array {
    if(!preg_match('/^[a-z0-9-]{2,64}$/',$key))throw new RuntimeException('Некорректная игра.');$pdo=db();$pdo->beginTransaction();try{$state=cc_profile_state($userId,true,$pdo);$f=$state['favorites'];$i=array_search($key,$f,true);if($i===false)$f[]=$key;else array_splice($f,$i,1);$state['favorites']=array_values(array_unique($f));cc_profile_state_save($pdo,$userId,$state);$pdo->commit();return$state['favorites'];}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw$e;}
}
function cc_division(int $level): array {return match(true){$level>=50=>['name'=>'Diamond','icon'=>'◆','next'=>null],$level>=35=>['name'=>'Platinum','icon'=>'✦','next'=>50],$level>=20=>['name'=>'Gold','icon'=>'★','next'=>35],$level>=10=>['name'=>'Silver','icon'=>'◈','next'=>20],$level>=5=>['name'=>'Bronze','icon'=>'⬢','next'=>10],default=>['name'=>'Starter','icon'=>'●','next'=>5]};}
function cc_recent_games(int $userId,int $limit=5): array {$limit=max(1,min(10,$limit));$q=db()->prepare("SELECT game_key,MAX(created_at) last_played,COUNT(*) rounds FROM game_rounds WHERE user_id=? GROUP BY game_key ORDER BY last_played DESC LIMIT {$limit}");$q->execute([$userId]);return$q->fetchAll();}
function cc_db_today(): string {static $day=null;if($day===null)$day=(string)db()->query('SELECT CURDATE()')->fetchColumn();return$day;}
function cc_daily_stats(int $userId): array {
    $q=db()->prepare("SELECT COUNT(*) rounds,COUNT(DISTINCT game_key) distinct_games,COALESCE(MAX(CASE WHEN bet_kopecks>0 THEN win_kopecks/bet_kopecks ELSE 0 END),0) best_mult,SUM(CASE WHEN game_key LIKE 'mini-%' THEN 1 ELSE 0 END) mini_rounds FROM game_rounds WHERE user_id=? AND created_at>=CURDATE()");$q->execute([$userId]);$r=$q->fetch()?:[];return['rounds'=>(int)($r['rounds']??0),'distinct_games'=>(int)($r['distinct_games']??0),'best_mult'=>(float)($r['best_mult']??0),'mini_rounds'=>(int)($r['mini_rounds']??0)];
}
function cc_daily_missions(int $userId): array {
    $s=cc_daily_stats($userId);$defs=[
        'warmup'=>['title'=>'Разминка','description'=>'Сыграйте 10 раундов сегодня','reward'=>10000,'current'=>$s['rounds'],'target'=>10],
        'explorer'=>['title'=>'Исследователь','description'=>'Сыграйте в 3 разных игры сегодня','reward'=>15000,'current'=>$s['distinct_games'],'target'=>3],
        'highfive'=>['title'=>'Высокий множитель','description'=>'Получите выигрыш не ниже ×5 за один раунд','reward'=>20000,'current'=>min(5,$s['best_mult']),'target'=>5],
        'arcade'=>['title'=>'Аркадный заход','description'=>'Сыграйте хотя бы одну мини-игру','reward'=>7500,'current'=>min(1,$s['mini_rounds']),'target'=>1],
    ];
    $q=db()->prepare("SELECT reference FROM wallet_transactions WHERE user_id=? AND type='mission_reward' AND created_at>=CURDATE()");$q->execute([$userId]);$claimed=array_flip(array_filter(array_map('strval',$q->fetchAll(PDO::FETCH_COLUMN))));$today=cc_db_today();
    foreach($defs as $key=>&$m){$m['key']=$key;$m['complete']=$m['current']>=$m['target'];$ref='mission:'.$today.':'.$key;$m['claimed']=isset($claimed[$ref]);$m['progress']=max(0,min(100,$m['target']>0?$m['current']/$m['target']*100:0));}$m=null;return array_values($defs);
}
function cc_claim_mission(int $userId,string $key): array {
    $missions=cc_daily_missions($userId);$mission=null;foreach($missions as $m)if($m['key']===$key){$mission=$m;break;}if(!$mission)throw new RuntimeException('Миссия не найдена.');if(!$mission['complete'])throw new RuntimeException('Миссия ещё не выполнена.');if($mission['claimed'])throw new RuntimeException('Награда уже получена.');$ref='mission:'.cc_db_today().':'.$key;$pdo=db();$pdo->beginTransaction();
    try{$u=$pdo->prepare('SELECT balance_kopecks FROM users WHERE id=? FOR UPDATE');$u->execute([$userId]);$row=$u->fetch();if(!$row)throw new RuntimeException('Аккаунт не найден.');$dup=$pdo->prepare("SELECT id FROM wallet_transactions WHERE user_id=? AND type='mission_reward' AND reference=? LIMIT 1");$dup->execute([$userId,$ref]);if($dup->fetch())throw new RuntimeException('Награда уже получена.');$before=(int)$row['balance_kopecks'];$after=$before+(int)$mission['reward'];$pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);wallet_entry($pdo,$userId,'mission_reward',(int)$mission['reward'],$after,$ref,['mission'=>$key]);$pdo->commit();return['reward'=>$mission['reward']/100,'balance_after'=>$after/100,'mission'=>$key];}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw$e;}
}
function cc_achievements(int $userId): array {
    $q=db()->prepare("SELECT COUNT(*) rounds,COUNT(DISTINCT game_key) games,COALESCE(MAX(CASE WHEN bet_kopecks>0 THEN win_kopecks/bet_kopecks ELSE 0 END),0) best_mult,SUM(CASE WHEN game_key LIKE 'mini-%' THEN 1 ELSE 0 END) mini_rounds,SUM(CASE WHEN mode IN ('buy_bonus','buy_super') THEN 1 ELSE 0 END) buys FROM game_rounds WHERE user_id=?");$q->execute([$userId]);$s=$q->fetch()?:[];$rounds=(int)($s['rounds']??0);$games=(int)($s['games']??0);$best=(float)($s['best_mult']??0);$mini=(int)($s['mini_rounds']??0);$buys=(int)($s['buys']??0);
    return[['icon'=>'✦','title'=>'Первый запуск','description'=>'Сыграть первый раунд','done'=>$rounds>=1,'progress'=>min(1,$rounds).'/1'],['icon'=>'⚡','title'=>'Сотня','description'=>'Сыграть 100 раундов','done'=>$rounds>=100,'progress'=>min(100,$rounds).'/100'],['icon'=>'🧭','title'=>'Исследователь','description'=>'Попробовать 5 разных игр','done'=>$games>=5,'progress'=>min(5,$games).'/5'],['icon'=>'◆','title'=>'Коллекционер','description'=>'Попробовать 10 разных игр','done'=>$games>=10,'progress'=>min(10,$games).'/10'],['icon'=>'×10','title'=>'Большой множитель','description'=>'Получить выигрыш ×10+','done'=>$best>=10,'progress'=>number_format(min(10,$best),1).'×/10×'],['icon'=>'●','title'=>'Аркадник','description'=>'Сыграть мини-игру','done'=>$mini>=1,'progress'=>min(1,$mini).'/1'],['icon'=>'B','title'=>'Охотник за бонусами','description'=>'Купить бонусный режим','done'=>$buys>=1,'progress'=>min(1,$buys).'/1'],['icon'=>'∞','title'=>'Тысяча','description'=>'Сыграть 1000 раундов','done'=>$rounds>=1000,'progress'=>min(1000,$rounds).'/1000']];
}
function cc_weekly_leaderboard(int $limit=20): array {$limit=max(3,min(50,$limit));$sql="SELECT u.username,COUNT(*) rounds,MAX(CASE WHEN r.bet_kopecks>0 THEN r.win_kopecks/r.bet_kopecks ELSE 0 END) best_mult,MAX(r.win_kopecks) best_win FROM game_rounds r JOIN users u ON u.id=r.user_id WHERE YEARWEEK(r.created_at,1)=YEARWEEK(CURDATE(),1) GROUP BY r.user_id,u.username HAVING best_mult>0 ORDER BY best_mult DESC,best_win DESC LIMIT {$limit}";return db()->query($sql)->fetchAll();}
function cc_mask_name(string $name): string {$len=mb_strlen($name);if($len<=2)return mb_substr($name,0,1).'***';return mb_substr($name,0,2).'***'.($len>6?mb_substr($name,-1):'');}
