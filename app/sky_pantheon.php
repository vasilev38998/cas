<?php
declare(strict_types=1);
require_once __DIR__.'/extra_games.php';

function sky_orb_value(): int {
    $pool=[2=>460,3=>270,4=>145,5=>78,8=>34,10=>20,15=>8,20=>5,25=>3,50=>1,100=>1];
    return (int)ag_pick($pool);
}
function sky_symbol(array $weights): string {$s=ag_pick($weights);return $s==='orb'?'orb'.sky_orb_value():$s;}
function sky_grid(int $cols,int $rows,array $weights): array {$g=[];for($i=0;$i<$cols*$rows;$i++)$g[]=sky_symbol($weights);return $g;}
function sky_collapse(array $grid,int $cols,int $rows,array $remove,array $weights): array {
    $rm=array_fill_keys($remove,true);$next=array_fill(0,$cols*$rows,null);
    for($c=0;$c<$cols;$c++){
        $kept=[];for($r=$rows-1;$r>=0;$r--){$i=$c*$rows+$r;if(!isset($rm[$i]))$kept[]=$grid[$i];}
        $w=$rows-1;foreach($kept as $s)$next[$c*$rows+$w--]=$s;while($w>=0)$next[$c*$rows+$w--]=sky_symbol($weights);
    }
    return $next;
}
function sky_eval(array $grid,array $pay,int $betK): array {
    $counts=[];$positions=[];$total=0;$wins=[];
    foreach($grid as $i=>$s){
        if($s==='scatter'||strpos($s,'orb')===0)continue;
        $counts[$s]=($counts[$s]??0)+1;$positions[$s][]=$i;
    }
    foreach($counts as $sym=>$count){
        if($count<10||!isset($pay[$sym]))continue;
        $tier=$count>=15?15:($count>=12?12:10);$factor=$pay[$sym][$tier]??0;if($factor<=0)continue;
        $amount=(int)round($betK*$factor);$total+=$amount;$wins[]=['symbol'=>$sym,'count'=>$count,'amount'=>$amount/100];
    }
    $all=[];foreach($wins as $w)$all=array_merge($all,$positions[$w['symbol']]??[]);
    return ['amount'=>$total,'positions'=>array_values(array_unique($all)),'wins'=>$wins];
}

function hv_sky(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=6;$r=5;
    $weights=['laurel'=>2200,'chalice'=>2000,'harp'=>1800,'ring'=>1600,'wing'=>1400,'thunder'=>1100,'orb'=>$isFree?180:80,'scatter'=>58];
    $pay=[
        'laurel'=>[10=>.18,12=>.45,15=>1.4],
        'chalice'=>[10=>.22,12=>.55,15=>1.7],
        'harp'=>[10=>.28,12=>.68,15=>2.1],
        'ring'=>[10=>.35,12=>.85,15=>2.6],
        'wing'=>[10=>.46,12=>1.1,15=>3.5],
        'thunder'=>[10=>.6,12=>1.4,15=>4.8],
    ];
    $grid=sky_grid($c,$r,$weights);$initial=$grid;$steps=[];$total=0;$charge=$isFree?max(1,(int)($state['sky_charge']??1)):1;
    for($t=1;$t<=10;$t++){
        $ev=sky_eval($grid,$pay,$betK);if($ev['amount']<=0)break;
        $orbPositions=[];$orbSum=0;
        foreach($grid as $i=>$s){if(strpos($s,'orb')===0){$orbPositions[]=$i;$orbSum+=(int)substr($s,3);}}
        if($isFree&&$orbSum>0)$charge=min(500,$charge+$orbSum);
        $mult=$isFree?$charge:($orbSum>0?$orbSum:1);
        $win=(int)round($ev['amount']*$mult);$total+=$win;
        $remove=array_values(array_unique(array_merge($ev['positions'],$orbPositions)));
        $grid=sky_collapse($grid,$c,$r,$remove,$weights);
        $steps[]=[
            'label'=>$orbSum>0?'НЕБЕСНАЯ СФЕРА ×'.$mult:'СОВПАДЕНИЕ 10+',
            'win'=>$win/100,
            'positions'=>$ev['positions'],
            'multiplier_positions'=>$orbPositions,
            'multiplier_total'=>$orbSum,
            'persistent_multiplier'=>$isFree?$charge:1,
            'grid_after'=>$grid,
            'extra'=>'Tumble '.$t,
            'fx'=>$orbSum>0?'sky-lightning':'sky-tumble'
        ];
    }
    $sc=ag_scatter_count($initial);$award=0;
    if($sc>=4){$award=$isFree?4:($sc>=6?12:($sc===5?10:8));$free+=$award;if(!$isFree)$state['sky_charge']=1;}
    if($free>0)$state['sky_charge']=$isFree?$charge:($state['sky_charge']??1);else unset($state['sky_charge']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'НЕБЕСНЫЙ ЗАРЯД ×'.max(1,(int)($state['sky_charge']??1)):'10+ В ЛЮБОМ МЕСТЕ • TUMBLE • СФЕРЫ']];
}
