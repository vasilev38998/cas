<?php
declare(strict_types=1);

require_once __DIR__.'/game_catalog.php';

function ag_pick(array $weights): string {
    $total=array_sum($weights); $r=random_int(1,$total);
    foreach($weights as $k=>$w){$r-=$w;if($r<=0)return (string)$k;}
    return (string)array_key_first($weights);
}
function ag_grid(int $cols,int $rows,array $weights): array {
    $g=[];for($i=0;$i<$cols*$rows;$i++)$g[]=ag_pick($weights);return $g;
}
function ag_scatter_count(array $grid,string $scatter='scatter'): int {
    $n=0;foreach($grid as $x)if($x===$scatter)$n++;return $n;
}
function ag_lines10(): array {
    return [[1,1,1,1,1],[0,0,0,0,0],[2,2,2,2,2],[0,1,2,1,0],[2,1,0,1,2],[0,0,1,2,2],[2,2,1,0,0],[1,0,0,0,1],[1,2,2,2,1],[0,1,1,1,0]];
}
function ag_lines20(): array {
    return [[0,0,0,0,0],[1,1,1,1,1],[2,2,2,2,2],[3,3,3,3,3],[0,1,2,1,0],[3,2,1,2,3],[1,0,0,0,1],[2,3,3,3,2],[0,0,1,2,3],[3,3,2,1,0],[0,1,1,1,0],[3,2,2,2,3],[1,2,3,2,1],[2,1,0,1,2],[0,1,2,3,2],[3,2,1,0,1],[1,1,2,3,3],[2,2,1,0,0],[0,2,0,2,0],[3,1,3,1,3]];
}
function ag_line_eval(array $grid,int $cols,int $rows,array $lines,array $pay,string $wild,int $betK): array {
    $total=0;$positions=[];$wins=[];$lineCount=max(1,count($lines));
    foreach($lines as $li=>$line){
        $idx=[];for($c=0;$c<$cols;$c++)$idx[]=$c*$rows+$line[$c];
        $base=null;foreach($idx as $i){$s=$grid[$i];if($s!==$wild&&$s!=='scatter'&&$s!=='coin'){$base=$s;break;}}
        if($base===null)$base=$wild;
        $count=0;$used=[];
        foreach($idx as $i){$s=$grid[$i];if($s===$base||$s===$wild){$count++;$used[]=$i;}else break;}
        if($count>=3 && isset($pay[$base][$count])){
            $amount=(int)round($betK*$pay[$base][$count]/$lineCount);$total+=$amount;$positions=array_merge($positions,$used);$wins[]=['line'=>$li+1,'symbol'=>$base,'count'=>$count,'amount'=>$amount/100];
        }
    }
    return ['amount'=>$total,'positions'=>array_values(array_unique($positions)),'wins'=>$wins];
}
function ag_collapse(array $grid,int $cols,int $rows,array $remove,array $weights): array {
    $rm=array_fill_keys($remove,true);$next=array_fill(0,$cols*$rows,null);
    for($c=0;$c<$cols;$c++){$kept=[];for($r=$rows-1;$r>=0;$r--){$i=$c*$rows+$r;if(!isset($rm[$i]))$kept[]=$grid[$i];}$w=$rows-1;foreach($kept as $s)$next[$c*$rows+$w--]=$s;while($w>=0)$next[$c*$rows+$w--]=ag_pick($weights);}
    return $next;
}
function ag_ways_eval(array $grid,int $cols,int $rows,array $pay,string $wild,int $betK,int $divisor,int $mult=1): array {
    $total=0;$positions=[];$detail=[];
    foreach($pay as $sym=>$table){
        $counts=[];$reelPositions=[];$reels=0;
        for($c=0;$c<$cols;$c++){$ps=[];for($r=0;$r<$rows;$r++){$i=$c*$rows+$r;$s=$grid[$i];if($s===$sym||$s===$wild)$ps[]=$i;}if(!$ps)break;$counts[]=count($ps);$reelPositions[]=$ps;$reels++;}
        if($reels>=3){$use=min($reels,max(array_keys($table)));while($use>=3&&!isset($table[$use]))$use--;if($use>=3){$ways=1;for($i=0;$i<$use;$i++)$ways*=$counts[$i];$amount=(int)round($betK*$table[$use]*$ways*$mult/$divisor);$total+=$amount;for($i=0;$i<$use;$i++)$positions=array_merge($positions,$reelPositions[$i]);$detail[]=['symbol'=>$sym,'reels'=>$use,'ways'=>$ways,'amount'=>$amount/100];}}
    }
    return ['amount'=>$total,'positions'=>array_values(array_unique($positions)),'wins'=>$detail];
}
function ag_neighbors(int $i,int $cols,int $rows): array {
    $c=intdiv($i,$rows);$r=$i%$rows;$o=[];if($r>0)$o[]=$i-1;if($r<$rows-1)$o[]=$i+1;if($c>0)$o[]=$i-$rows;if($c<$cols-1)$o[]=$i+$rows;return $o;
}
function ag_clusters(array $grid,int $cols,int $rows,array $excluded=['bomb','scatter']): array {
    $seen=[];$out=[];$n=$cols*$rows;
    for($i=0;$i<$n;$i++){if(isset($seen[$i])||in_array($grid[$i],$excluded,true))continue;$sym=$grid[$i];$stack=[$i];$cells=[];$seen[$i]=true;while($stack){$cur=array_pop($stack);$cells[]=$cur;foreach(ag_neighbors($cur,$cols,$rows) as $j)if(!isset($seen[$j])&&$grid[$j]===$sym){$seen[$j]=true;$stack[]=$j;}}if(count($cells)>=5)$out[]=['symbol'=>$sym,'cells'=>$cells];}
    return $out;
}
function ag_cluster_factor(int $count): float {
    if($count>=25)return 8;if($count>=20)return 5;if($count>=16)return 3;if($count>=12)return 1.5;if($count>=9)return .8;if($count>=7)return .45;return .25;
}

function ag_engine_fruit(int $betK,bool $isFree,int &$free,array &$state): array {
    $cols=5;$rows=4;$weights=['cherry'=>1800,'lemon'=>1750,'grape'=>1650,'bell'=>1250,'seven'=>950,'diamond'=>700,'wild'=>450,'scatter'=>220];
    $pay=['cherry'=>[3=>1,4=>3,5=>8],'lemon'=>[3=>1.1,4=>3.5,5=>9],'grape'=>[3=>1.3,4=>4,5=>11],'bell'=>[3=>1.8,4=>6,5=>16],'seven'=>[3=>2.5,4=>9,5=>25],'diamond'=>[3=>4,4=>15,5=>45],'wild'=>[3=>5,4=>20,5=>60]];
    $grid=ag_grid($cols,$rows,$weights);$sticky=$state['sticky']??[];
    if($isFree){foreach($sticky as $p)if(isset($grid[$p]))$grid[$p]='wild';foreach($grid as $i=>$s)if($s==='wild')$sticky[$i]=$i;$sticky=array_values($sticky);$state['sticky']=$sticky;}
    $eval=ag_line_eval($grid,$cols,$rows,ag_lines20(),$pay,'wild',$betK);$sc=ag_scatter_count($grid);$award=0;
    if($sc>=3){$award=$isFree?5:($sc>=5?16:($sc===4?12:8));$free+=$award;if(!$isFree)$state['sticky']=[];}
    if($free<=0)unset($state['sticky']);
    $steps=[];if($eval['amount']>0)$steps[]=['label'=>'ЛИНИИ','win'=>$eval['amount']/100,'positions'=>$eval['positions'],'grid_after'=>$grid];
    return ['win'=>$eval['amount'],'payload'=>['initial_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'ЛИПКИЕ ВАЙЛДЫ':'20 ЛИНИЙ','sticky'=>array_values($state['sticky']??[])]];
}

function ag_engine_temple(int $betK,bool $isFree,int &$free,array &$state): array {
    $cols=6;$rows=5;$weights=['mask'=>1800,'idol'=>1650,'bird'=>1550,'snake'=>1450,'gem'=>1200,'torch'=>1050,'wild'=>420,'scatter'=>170];
    $pay=['mask'=>[3=>.6,4=>1.2,5=>2.4,6=>5],'idol'=>[3=>.7,4=>1.4,5=>2.8,6=>6],'bird'=>[3=>.8,4=>1.7,5=>3.5,6=>7],'snake'=>[3=>1,4=>2.2,5=>4.5,6=>9],'gem'=>[3=>1.3,4=>3,5=>6,6=>12],'torch'=>[3=>1.7,4=>4,5=>8,6=>16]];
    $grid=ag_grid($cols,$rows,$weights);$initial=$grid;$mult=$isFree?(int)($state['free_mult']??1):1;$steps=[];$total=0;
    for($cascade=1;$cascade<=12;$cascade++){$ev=ag_ways_eval($grid,$cols,$rows,$pay,'wild',$betK,40,$mult);if($ev['amount']<=0)break;$total+=$ev['amount'];$remove=$ev['positions'];$grid=ag_collapse($grid,$cols,$rows,$remove,$weights);$steps[]=['label'=>'ПУТИ ×'.$mult,'win'=>$ev['amount']/100,'positions'=>$remove,'grid_after'=>$grid,'extra'=>'Каскад '.$cascade];$mult=min($isFree?10:6,$mult+1);}
    $sc=ag_scatter_count($initial);$award=0;if($sc>=3){$award=$isFree?5:($sc>=5?15:($sc===4?12:10));$free+=$award;}
    if($free>0)$state['free_mult']=$isFree?$mult:1;else unset($state['free_mult']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'МНОЖИТЕЛЬ ФРИСПИНОВ ×'.max(1,(int)($state['free_mult']??1)):'КАСКАДНЫЕ ПУТИ']];
}

function ag_engine_jungle(int $betK,bool $isFree,int &$free,array &$state): array {
    $cols=5;$rows=3;$weights=['parrot'=>1900,'tiger'=>1450,'leaf'=>2100,'drum'=>1700,'gem'=>1150,'wild'=>500,'coin'=>850];
    $pay=['parrot'=>[3=>1,4=>3,5=>8],'tiger'=>[3=>2,4=>6,5=>18],'leaf'=>[3=>.8,4=>2.5,5=>7],'drum'=>[3=>1.2,4=>4,5=>11],'gem'=>[3=>2.8,4=>9,5=>28],'wild'=>[3=>4,4=>15,5=>45]];
    $grid=ag_grid($cols,$rows,$weights);$ev=ag_line_eval($grid,$cols,$rows,ag_lines10(),$pay,'wild',$betK);$steps=[];$total=$ev['amount'];if($ev['amount']>0)$steps[]=['label'=>'ЛИНИИ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid];
    $coinPos=[];foreach($grid as $i=>$s)if($s==='coin')$coinPos[]=$i;$feature=null;
    if(count($coinPos)>=6){$held=[];$coinPool=[50,100,100,150,200,300,500,1000];foreach($coinPos as $p)$held[$p]=$coinPool[array_rand($coinPool)];$respins=3;$frames=[];$guard=0;
        while($respins>0&&count($held)<$cols*$rows&&$guard++<30){$new=[];for($i=0;$i<$cols*$rows;$i++){if(isset($held[$i]))continue;if(random_int(1,100)<=18){$v=$coinPool[array_rand($coinPool)];$held[$i]=$v;$new[$i]=$v;}}if($new)$respins=3;else$respins--;$frames[]=['respins'=>$respins,'coins'=>$held,'new'=>array_keys($new)];}
        $featureK=0;foreach($held as $factor100)$featureK+=(int)round($betK*$factor100/100);$full=count($held)===$cols*$rows;if($full)$featureK+=$betK*50;$total+=$featureK;$feature=['type'=>'hold','title'=>'ЗОЛОТОЙ ТОТЕМ','frames'=>$frames,'coins'=>$held,'full'=>$full,'win'=>$featureK/100];
    }
    return ['win'=>$total,'payload'=>['initial_grid'=>$grid,'steps'=>$steps,'feature'=>$feature,'free_spins_awarded'=>0,'badge'=>'ЗОЛОТЫЕ РЕСПИНЫ']];
}

function ag_engine_crystal(int $betK,bool $isFree,int &$free,array &$state): array {
    $cols=8;$rows=8;$weights=['ruby'=>2050,'emerald'=>2050,'sapphire'=>2050,'amethyst'=>1950,'sunstone'=>1850,'bomb'=>$isFree?700:350,'scatter'=>120];
    $grid=ag_grid($cols,$rows,$weights);$initial=$grid;$steps=[];$total=0;
    for($cascade=1;$cascade<=12;$cascade++){$clusters=ag_clusters($grid,$cols,$rows);if(!$clusters)break;$remove=[];$amount=0;foreach($clusters as $cl){$remove=array_merge($remove,$cl['cells']);$amount+=(int)round($betK*ag_cluster_factor(count($cl['cells'])));}$remove=array_values(array_unique($remove));$bombs=[];foreach($remove as $p)foreach(ag_neighbors($p,$cols,$rows) as $n)if(($grid[$n]??'')==='bomb')$bombs[$n]=true;$bombBonus=0;$exploded=[];foreach(array_keys($bombs) as $b){$bc=intdiv($b,$rows);$br=$b%$rows;for($dc=-1;$dc<=1;$dc++)for($dr=-1;$dr<=1;$dr++){$c=$bc+$dc;$r=$br+$dr;if($c>=0&&$c<$cols&&$r>=0&&$r<$rows)$exploded[]=$c*$rows+$r;}$bonus=[50,100,150,200,300][array_rand([50,100,150,200,300])];$bombBonus+=(int)round($betK*$bonus/100);}$remove=array_values(array_unique(array_merge($remove,$exploded)));$amount+=$bombBonus;$total+=$amount;$grid=ag_collapse($grid,$cols,$rows,$remove,$weights);$steps[]=['label'=>$bombs?'ЦЕПНОЙ ВЗРЫВ':'КЛАСТЕРЫ','win'=>$amount/100,'positions'=>$remove,'grid_after'=>$grid,'extra'=>$bombs?('Бомб: '.count($bombs)):'Каскад '.$cascade];}
    $sc=ag_scatter_count($initial);$award=0;if($sc>=4){$award=$isFree?3:($sc>=6?10:($sc===5?8:6));$free+=$award;}
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'РЕАКТОР: БОМБЫ УСИЛЕНЫ':'ЦЕПНАЯ РЕАКЦИЯ']];
}

function ag_engine_scroll(int $betK,bool $isFree,int &$free,array &$state): array {
    $cols=5;$rows=3;$weights=['falcon'=>1600,'scarab'=>1900,'lotus'=>2100,'ankh'=>1750,'crown'=>1200,'wild'=>480,'scatter'=>260];
    $pay=['falcon'=>[3=>1.6,4=>5,5=>14],'scarab'=>[3=>1.1,4=>3.5,5=>10],'lotus'=>[3=>.8,4=>2.5,5=>7],'ankh'=>[3=>1.3,4=>4,5=>12],'crown'=>[3=>2.5,4=>9,5=>28],'wild'=>[3=>4,4=>15,5=>45]];
    $grid=ag_grid($cols,$rows,$weights);$chosen=$state['chosen']??null;$expanded=[];
    if($isFree&&$chosen){for($c=0;$c<$cols;$c++){$hit=false;for($r=0;$r<$rows;$r++)if($grid[$c*$rows+$r]===$chosen)$hit=true;if($hit){$expanded[]=$c;for($r=0;$r<$rows;$r++)$grid[$c*$rows+$r]=$chosen;}}}
    $ev=ag_line_eval($grid,$cols,$rows,ag_lines10(),$pay,'wild',$betK);$sc=ag_scatter_count($grid);$award=0;
    if($sc>=3){if($isFree){$award=5;}else{$award=$sc>=5?16:($sc===4?12:8);$regular=['falcon','scarab','lotus','ankh','crown'];$chosen=$regular[array_rand($regular)];$state['chosen']=$chosen;}$free+=$award;}
    if($free<=0)unset($state['chosen']);$steps=[];if($ev['amount']>0)$steps[]=['label'=>$expanded?'РАСШИРЕНИЕ':'ЛИНИИ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>$expanded?('Барабаны: '.implode(',',array_map(fn($x)=>$x+1,$expanded))):''];
    return ['win'=>$ev['amount'],'payload'=>['initial_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree&&$chosen?'ВЫБРАННЫЙ СИМВОЛ: '.$chosen:'СВИТОК СОЛНЦА','chosen'=>$chosen,'expanded_reels'=>$expanded]];
}

function ag_engine_neon(int $betK,bool $isFree,int &$free,array &$state): array {
    $cols=5;$rows=5;$weights=['cyan'=>1900,'pink'=>1900,'lime'=>1800,'violet'=>1650,'orange'=>1500,'wild'=>450,'scatter'=>190];
    $pay=['cyan'=>[3=>.5,4=>1.1,5=>2.5],'pink'=>[3=>.55,4=>1.2,5=>2.8],'lime'=>[3=>.65,4=>1.4,5=>3.2],'violet'=>[3=>.8,4=>1.8,5=>4],'orange'=>[3=>1,4=>2.2,5=>5]];
    $grid=ag_grid($cols,$rows,$weights);$initial=$grid;$voltage=$isFree?max(1,(int)($state['voltage']??1)):1;$steps=[];$total=0;
    for($t=1;$t<=12;$t++){$ev=ag_ways_eval($grid,$cols,$rows,$pay,'wild',$betK,45,$voltage);if($ev['amount']<=0)break;$total+=$ev['amount'];$grid=ag_collapse($grid,$cols,$rows,$ev['positions'],$weights);$steps[]=['label'=>'НАПРЯЖЕНИЕ ×'.$voltage,'win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>'Tumble '.$t];$voltage=min($isFree?10:5,$voltage+1);}
    $sc=ag_scatter_count($initial);$award=0;if($sc>=3){$award=$isFree?3:($sc>=5?15:($sc===4?10:7));$free+=$award;if(!$isFree)$state['voltage']=1;}
    if($free>0)$state['voltage']=$isFree?$voltage:($state['voltage']??1);else unset($state['voltage']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'НЕОНОВОЕ НАПРЯЖЕНИЕ ×'.max(1,(int)($state['voltage']??1)):'TUMBLE-РАЗГОН']];
}

function arcade_game_spin(PDO $pdo,int $userId,string $gameKey,int $betRub): array {
    $allowed=[10,20,50,100,200,500,1000];if(!in_array($betRub,$allowed,true))throw new RuntimeException('Недопустимая ставка.');
    $engines=['fruit-fiesta'=>'ag_engine_fruit','temple-ways'=>'ag_engine_temple','jungle-hold'=>'ag_engine_jungle','crystal-clusters'=>'ag_engine_crystal','sun-scroll'=>'ag_engine_scroll','neon-rush'=>'ag_engine_neon'];
    if(!isset($engines[$gameKey]))throw new RuntimeException('Игра не найдена.');$betK=$betRub*100;$pdo->beginTransaction();
    try{$q=$pdo->prepare('SELECT id,balance_kopecks FROM users WHERE id=? FOR UPDATE');$q->execute([$userId]);$u=$q->fetch();if(!$u)throw new RuntimeException('Аккаунт не найден.');$before=(int)$u['balance_kopecks'];
        $q=$pdo->prepare('SELECT free_spins,multiplier_map_json FROM user_game_states WHERE user_id=? AND game_key=? FOR UPDATE');$q->execute([$userId,$gameKey]);$gs=$q->fetch();if(!$gs){$pdo->prepare('INSERT INTO user_game_states(user_id,game_key,multiplier_map_json) VALUES(?,?,?)')->execute([$userId,$gameKey,'{}']);$gs=['free_spins'=>0,'multiplier_map_json'=>'{}'];}
        $free=(int)$gs['free_spins'];$state=json_decode((string)$gs['multiplier_map_json'],true);if(!is_array($state))$state=[];$isFree=$free>0;if($isFree)$free--;$cost=$isFree?0:$betK;if($before<$cost)throw new RuntimeException('Недостаточно виртуальных средств.');
        $engine=$engines[$gameKey];$result=$engine($betK,$isFree,$free,$state);$win=max(0,(int)$result['win']);$cap=$betK*10000;if($win>$cap)$win=$cap;$after=$before-$cost+$win;
        $pdo->prepare('UPDATE users SET balance_kopecks=? WHERE id=?')->execute([$after,$userId]);$pdo->prepare('UPDATE user_game_states SET free_spins=?,storm_charge=0,multiplier_map_json=? WHERE user_id=? AND game_key=?')->execute([$free,json_encode($state,JSON_UNESCAPED_UNICODE),$userId,$gameKey]);
        $summary=['free'=>$isFree,'free_spins_after'=>$free,'feature'=>!empty($result['payload']['feature'])];$pdo->prepare('INSERT INTO game_rounds(user_id,game_key,mode,bet_kopecks,cost_kopecks,win_kopecks,balance_before_kopecks,balance_after_kopecks,result_json) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$userId,$gameKey,$isFree?'free':'normal',$betK,$cost,$win,$before,$after,json_encode($summary,JSON_UNESCAPED_UNICODE)]);$round=(int)$pdo->lastInsertId();$ref='round:'.$round;if($cost>0)wallet_entry($pdo,$userId,'game_bet',-$cost,$before-$cost,$ref,['game'=>$gameKey,'bet'=>$betRub]);if($win>0)wallet_entry($pdo,$userId,'game_win',$win,$after,$ref,['game'=>$gameKey]);$pdo->commit();
        return array_merge(['ok'=>true,'round_id'=>$round,'game'=>$gameKey,'bet'=>$betRub,'is_free_spin'=>$isFree,'cost'=>$cost/100,'balance_before'=>$before/100,'balance_after'=>$after/100,'total_win'=>$win/100,'free_spins'=>$free],$result['payload']);
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
