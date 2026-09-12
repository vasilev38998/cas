<?php
declare(strict_types=1);
require_once __DIR__.'/high_volatility_engines.php';

function hv_curtains(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=4;
    $weights=['rose'=>2100,'fan'=>1950,'mask'=>1750,'crown'=>1350,'gem'=>950,'wild'=>$isFree?190:95,'scatter'=>75];
    $pay=['rose'=>[3=>1.1,4=>3.4,5=>10],'fan'=>[3=>1.3,4=>4.1,5=>12],'mask'=>[3=>1.6,4=>5.2,5=>16],'crown'=>[3=>2.3,4=>8,5=>25],'gem'=>[3=>3.8,4=>14,5=>45],'wild'=>[3=>6,4=>22,5=>70]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$curtains=[];$reveal=null;
    $trigger=$isFree || random_int(1,100)<=9;
    if($trigger){
        $count=$isFree?2:(random_int(1,100)<=24?2:1);$cols=range(0,$c-1);shuffle($cols);$curtains=array_slice($cols,0,$count);
        $pool=['rose'=>30,'fan'=>26,'mask'=>20,'crown'=>13,'gem'=>8,'wild'=>$isFree?3:1];$reveal=ag_pick($pool);
        foreach($curtains as $col)for($row=0;$row<$r;$row++)$grid[$col*$r+$row]=$reveal;
    }
    $ev=ag_line_eval($grid,$c,$r,hv_lines8_4(),$pay,'wild',$betK);$sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?12:($sc===4?10:8));$free+=$award;}
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>$curtains?'ЗАНАВЕС ОТКРЫТ':'8 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'fx'=>$curtains?'curtain-win':'velvet-win'];
    return ['win'=>$ev['amount'],'payload'=>[
        'initial_grid'=>$initial,'display_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,
        'curtains'=>$curtains,'curtain_symbol'=>$reveal,'badge'=>$isFree?'БОНУС: ДВОЙНОЙ ЗАНАВЕС':'ЗАНАВЕС МОЖЕТ ЗАКРЫТЬ БАРАБАН'
    ]];
}

function hv_mystery(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=4;
    $weights=['key'=>2050,'watch'=>1900,'ring'=>1700,'pearl'=>1500,'gem'=>1050,'mystery'=>$isFree?420:230,'wild'=>95,'scatter'=>65];
    $pay=['key'=>[3=>1,4=>3.1,5=>9],'watch'=>[3=>1.25,4=>4,5=>12],'ring'=>[3=>1.55,4=>5,5=>16],'pearl'=>[3=>2,4=>7,5=>22],'gem'=>[3=>3.5,4=>13,5=>42],'wild'=>[3=>6,4=>22,5=>70]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$mystery=[];foreach($grid as $i=>$s)if($s==='mystery')$mystery[]=$i;$reveal=null;
    if($mystery){$pool=['key'=>29,'watch'=>25,'ring'=>20,'pearl'=>15,'gem'=>9,'wild'=>2];$reveal=ag_pick($pool);foreach($mystery as $i)$grid[$i]=$reveal;}
    $ev=ag_line_eval($grid,$c,$r,ag_lines10(),$pay,'wild',$betK);$sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?12:($sc===4?10:8));$free+=$award;}
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>$mystery?'ТАЙНЫЙ СИМВОЛ: '.strtoupper((string)$reveal):'10 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'fx'=>$mystery?'mystery-reveal':'vault-win'];
    return ['win'=>$ev['amount'],'payload'=>[
        'initial_grid'=>$initial,'display_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,
        'mystery_positions'=>$mystery,'mystery_symbol'=>$reveal,'badge'=>$isFree?'БОНУС: БОЛЬШЕ ТАЙНЫХ ЯЧЕЕК':'ТАЙНЫЕ ЯЧЕЙКИ ПРЕВРАЩАЮТСЯ В ОДИН СИМВОЛ'
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
