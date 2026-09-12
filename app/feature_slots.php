<?php
declare(strict_types=1);
require_once __DIR__.'/high_volatility_engines.php';

function feature_lines6_4(): array {
    return [[0,0,0,0,0],[1,1,1,1,1],[2,2,2,2,2],[3,3,3,3,3],[0,1,2,1,0],[3,2,1,2,3]];
}
function hv_curtains(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=4;
    $weights=['rose'=>2150,'fan'=>2000,'mask'=>1800,'crown'=>1375,'gem'=>950,'wild'=>$isFree?180:85,'scatter'=>68];
    $pay=['rose'=>[3=>1.2,4=>3.7,5=>11],'fan'=>[3=>1.4,4=>4.5,5=>13],'mask'=>[3=>1.75,4=>5.7,5=>17],'crown'=>[3=>2.5,4=>8.7,5=>27],'gem'=>[3=>4.1,4=>15,5=>48],'wild'=>[3=>6.5,4=>24,5=>75]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$curtains=[];$reveal=null;
    $trigger=$isFree || random_int(1,100)<=7;
    if($trigger){
        $count=$isFree?2:(random_int(1,100)<=20?2:1);$cols=range(0,$c-1);shuffle($cols);$curtains=array_slice($cols,0,$count);
        $pool=['rose'=>31,'fan'=>27,'mask'=>20,'crown'=>12,'gem'=>8,'wild'=>$isFree?2:1];$reveal=ag_pick($pool);
        foreach($curtains as $col)for($row=0;$row<$r;$row++)$grid[$col*$r+$row]=$reveal;
    }
    $ev=ag_line_eval($grid,$c,$r,feature_lines6_4(),$pay,'wild',$betK);$sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?12:($sc===4?10:8));$free+=$award;}
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>$curtains?'ЗАНАВЕС ОТКРЫТ':'6 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'fx'=>$curtains?'curtain-win':'velvet-win'];
    return ['win'=>$ev['amount'],'payload'=>[
        'initial_grid'=>$initial,'display_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,
        'curtains'=>$curtains,'curtain_symbol'=>$reveal,'badge'=>$isFree?'БОНУС: ДВОЙНОЙ ЗАНАВЕС':'РЕДКИЙ ЗАНАВЕС • 6 ЛИНИЙ'
    ]];
}

function hv_mystery(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=4;
    $weights=['key'=>2100,'watch'=>1950,'ring'=>1750,'pearl'=>1525,'gem'=>1075,'mystery'=>$isFree?360:170,'wild'=>85,'scatter'=>60];
    $pay=['key'=>[3=>1.1,4=>3.4,5=>10],'watch'=>[3=>1.35,4=>4.4,5=>13],'ring'=>[3=>1.7,4=>5.5,5=>17],'pearl'=>[3=>2.2,4=>7.7,5=>24],'gem'=>[3=>3.8,4=>14,5=>45],'wild'=>[3=>6.5,4=>24,5=>75]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$mystery=[];foreach($grid as $i=>$s)if($s==='mystery')$mystery[]=$i;$reveal=null;
    if($mystery){$pool=['key'=>30,'watch'=>26,'ring'=>20,'pearl'=>15,'gem'=>8,'wild'=>1];$reveal=ag_pick($pool);foreach($mystery as $i)$grid[$i]=$reveal;}
    $ev=ag_line_eval($grid,$c,$r,feature_lines6_4(),$pay,'wild',$betK);$sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?12:($sc===4?10:8));$free+=$award;}
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>$mystery?'ТАЙНЫЙ СИМВОЛ: '.strtoupper((string)$reveal):'6 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'fx'=>$mystery?'mystery-reveal':'vault-win'];
    return ['win'=>$ev['amount'],'payload'=>[
        'initial_grid'=>$initial,'display_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,
        'mystery_positions'=>$mystery,'mystery_symbol'=>$reveal,'badge'=>$isFree?'БОНУС: БОЛЬШЕ ТАЙНЫХ ЯЧЕЕК':'РЕДКИЕ MYSTERY • 6 ЛИНИЙ'
    ]];
}

function arcade_forced_bonus_grid(string $gameKey,int $cols,int $rows,array $symbols,int $scatterCount): array {
    $regular=[];foreach($symbols as $k=>$v)if($k!=='scatter' && $k!=='coin')$regular[]=$k;if(!$regular)$regular=array_keys($symbols);
    $grid=[];for($i=0;$i<$cols*$rows;$i++)$grid[]=$regular[array_rand($regular)];
    if(isset($symbols['scatter'])){$pos=range(0,$cols*$rows-1);shuffle($pos);foreach(array_slice($pos,0,$scatterCount) as $p)$grid[$p]='scatter';}
    return $grid;
}

function arcade_jungle_buy(int $betK): array {
    $c=5;$r=3;$regular=['parrot','tiger','leaf','drum','gem','wild'];$grid=[];for($i=0;$i<15;$i++)$grid[]=$regular[array_rand($regular)];$pos=range(0,14);shuffle($pos);$coinPos=array_slice($pos,0,6);foreach($coinPos as $p)$grid[$p]='coin';
    $pool=[50,75,100,100,125,150,200,300,500,1000];$held=[];foreach($coinPos as $p)$held[$p]=$pool[array_rand($pool)];$respins=3;$frames=[];$guard=0;
    while($respins>0&&count($held)<15&&$guard++<35){$new=[];for($i=0;$i<15;$i++){if(isset($held[$i]))continue;if(random_int(1,100)<=13){$v=$pool[array_rand($pool)];$held[$i]=$v;$new[$i]=$v;}}if($new)$respins=3;else$respins--;$frames[]=['respins'=>$respins,'coins'=>$held,'new'=>array_keys($new)];}
    $win=0;foreach($held as $f)$win+=(int)round($betK*$f/100);$full=count($held)===15;if($full)$win+=$betK*75;
    return ['win'=>$win,'payload'=>['initial_grid'=>$grid,'steps'=>[],'feature'=>['type'=>'hold','title'=>'КУПЛЕННЫЙ ЗОЛОТОЙ ТОТЕМ','frames'=>$frames,'coins'=>$held,'full'=>$full,'win'=>$win/100],'free_spins_awarded'=>0,'badge'=>'BONUS BUY • HOLD & RESPIN']];
}
