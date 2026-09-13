<?php
declare(strict_types=1);
require_once __DIR__.'/extra_games.php';

function exp_count_positions(array $grid,array $symbols,string $wild='wild'): array {
    $wildPos=[];$pos=[];foreach($grid as $i=>$s){if($s===$wild){$wildPos[]=$i;continue;}if(in_array($s,$symbols,true))$pos[$s][]=$i;}
    $out=[];foreach($symbols as $s)$out[$s]=array_values(array_unique(array_merge($pos[$s]??[],$wildPos)));return$out;
}
function exp_tier_factor(int $count,array $tiers): float {krsort($tiers,SORT_NUMERIC);foreach($tiers as $min=>$factor)if($count>=(int)$min)return(float)$factor;return 0.0;}

/** Грозовая кузница: редкие scatter-pays 10+, каскады и растущий множитель ковки. */
function hv_forge_tempest(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=6;$r=5;$regular=['ember','hammer','rune','shield','crown','thunder'];
    $weights=['ember'=>1880,'hammer'=>1760,'rune'=>1620,'shield'=>1460,'crown'=>1180,'thunder'=>960,'wild'=>$isFree?100:45,'scatter'=>54];
    $base=['ember'=>1.0,'hammer'=>1.08,'rune'=>1.18,'shield'=>1.35,'crown'=>1.65,'thunder'=>2.05];
    $tiers=[10=>.18,12=>.38,14=>.78,17=>1.60,20=>3.20,24=>6.50,28=>14.0];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$steps=[];$total=0;$mult=$isFree?max(1,(int)($state['forge_mult']??1)):1;
    for($cascade=1;$cascade<=10;$cascade++){
        $positions=exp_count_positions($grid,$regular,'wild');$remove=[];$amount=0;$wins=[];
        foreach($regular as $sym){$cells=$positions[$sym]??[];$n=count($cells);$factor=exp_tier_factor($n,$tiers);if($factor<=0)continue;$part=(int)round($betK*$factor*$base[$sym]*$mult);if($part<=0)continue;$amount+=$part;$remove=array_merge($remove,$cells);$wins[]=['symbol'=>$sym,'count'=>$n,'multiplier'=>$mult,'amount'=>$part/100];}
        if($amount<=0)break;$remove=array_values(array_unique($remove));$total+=$amount;$grid=ag_collapse($grid,$c,$r,$remove,$weights);
        $steps[]=['label'=>'КОВКА ×'.$mult,'win'=>$amount/100,'positions'=>$remove,'grid_after'=>$grid,'extra'=>'Каскад '.$cascade.' • заряд +1','fx'=>'forge-surge','wins'=>$wins];
        $mult=min($isFree?12:6,$mult+1);
    }
    $sc=ag_scatter_count($initial);$award=0;if($sc>=4){$award=$isFree?3:($sc>=6?12:($sc===5?10:8));$free+=$award;if(!$isFree)$state['forge_mult']=1;}
    if($free>0)$state['forge_mult']=$isFree?$mult:max(1,(int)($state['forge_mult']??1));else unset($state['forge_mult']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'ГРОЗОВАЯ КОВКА ×'.max(1,(int)($state['forge_mult']??1)):'10+ В ЛЮБОМ МЕСТЕ • КАСКАДЫ','forge_multiplier'=>$mult]];
}

/** Лунный зверинец: линии с более редкими low-symbol hits и случайные полные wild-колонны. */
function hv_lunar_beasts(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=4;$weights=['fox'=>2050,'owl'=>1900,'wolf'=>1660,'stag'=>1390,'lynx'=>1120,'wild'=>72,'scatter'=>64];
    $pay=['fox'=>[4=>2.4,5=>8.0],'owl'=>[4=>3.0,5=>10.0],'wolf'=>[4=>4.2,5=>14.0],'stag'=>[3=>1.8,4=>7.0,5=>24.0],'lynx'=>[3=>3.0,4=>11.0,5=>38.0],'wild'=>[3=>5.5,4=>21.0,5=>68.0]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$phase=$isFree?max(1,(int)($state['moon_phase']??1)):1;$moonCols=[];
    $chance=$isFree?min(42,12+$phase*5):4;$roll=random_int(1,100);
    if($roll<=$chance){$cols=range(0,$c-1);shuffle($cols);$count=($isFree&&$roll<=max(3,(int)floor($chance*.18)))?2:1;$moonCols=array_slice($cols,0,$count);foreach($moonCols as $col)for($row=0;$row<$r;$row++)$grid[$col*$r+$row]='wild';}
    $ev=ag_line_eval($grid,$c,$r,ag_lines20(),$pay,'wild',$betK);$sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?13:($sc===4?10:8));$free+=$award;if(!$isFree)$phase=1;}
    if($isFree)$phase=min(6,$phase+1);if($free>0)$state['moon_phase']=$phase;else unset($state['moon_phase']);
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>$moonCols?'ЛУННЫЙ ВАЙЛД':'20 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>$moonCols?('Полных колонн: '.count($moonCols)):'','fx'=>$moonCols?'moon-rise':'lunar-hit'];
    return ['win'=>$ev['amount'],'payload'=>['initial_grid'=>$initial,'display_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'moon_columns'=>$moonCols,'moon_phase'=>$phase,'badge'=>$isFree?'ФАЗА ЛУНЫ '.$phase.' / 6':'РЕДКИЕ ЛУННЫЕ WILD-КОЛОННЫ']];
}

function exp_clockwork_clusters(array $grid,int $cols,int $rows,int $min=6): array {
    $raw=ag_clusters($grid,$cols,$rows,['gear','scatter']);return array_values(array_filter($raw,fn($x)=>count($x['cells'])>=$min));
}
function exp_clockwork_factor(int $n): float {return $n>=22?19.0:($n>=18?10.5:($n>=14?5.4:($n>=11?2.85:($n>=8?1.45:.72))));}

/** Механический импульс: редкие кластеры с более сильной выплатой и крестовые шестерни. */
function hv_clockwork_shift(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=5;$weights=['copper'=>2150,'sapphire'=>2020,'emerald'=>1940,'ruby'=>1770,'clock'=>1510,'gear'=>$isFree?330:165,'scatter'=>58];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$steps=[];$total=0;$gearPower=$isFree?max(1,(int)($state['gear_power']??1)):1;
    for($cascade=1;$cascade<=11;$cascade++){
        $clusters=exp_clockwork_clusters($grid,$c,$r,6);if(!$clusters)break;$remove=[];$amount=0;
        foreach($clusters as $cl){$remove=array_merge($remove,$cl['cells']);$amount+=(int)round($betK*exp_clockwork_factor(count($cl['cells'])));}
        $remove=array_values(array_unique($remove));$triggered=[];
        foreach($remove as $p)foreach(ag_neighbors($p,$c,$r) as $n)if(($grid[$n]??'')==='gear')$triggered[$n]=true;
        $cross=[];$gearBonus=0;foreach(array_keys($triggered) as $g){$col=intdiv($g,$r);$row=$g%$r;for($rr=0;$rr<$r;$rr++)$cross[]=$col*$r+$rr;for($cc=0;$cc<$c;$cc++)$cross[]=$cc*$r+$row;$gearBonus+=(int)round($betK*.45*$gearPower);if($isFree)$gearPower=min(6,$gearPower+1);}
        $remove=array_values(array_unique(array_merge($remove,$cross)));$amount+=$gearBonus;$total+=$amount;$grid=ag_collapse($grid,$c,$r,$remove,$weights);
        $steps[]=['label'=>$triggered?'ШЕСТЕРНИ ×'.$gearPower:'КЛАСТЕР 6+','win'=>$amount/100,'positions'=>$remove,'grid_after'=>$grid,'extra'=>$triggered?('Крестов: '.count($triggered).' • каскад '.$cascade):('Каскад '.$cascade),'fx'=>$triggered?'gear-cross':'clockwork-hit'];
    }
    $sc=ag_scatter_count($initial);$award=0;if($sc>=4){$award=$isFree?3:($sc>=6?11:($sc===5?9:7));$free+=$award;if(!$isFree)$state['gear_power']=1;}
    if($free>0)$state['gear_power']=$isFree?$gearPower:max(1,(int)($state['gear_power']??1));else unset($state['gear_power']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'gear_power'=>$gearPower,'badge'=>$isFree?'МОЩНОСТЬ ШЕСТЕРНИ ×'.$gearPower:'КЛАСТЕРЫ 6+ • КРЕСТОВЫЕ ШЕСТЕРНИ']];
}
