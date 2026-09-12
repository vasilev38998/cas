<?php
declare(strict_types=1);
require_once __DIR__.'/high_volatility_engines.php';

function hv3_temple(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=6;$r=5;
    $weights=['mask'=>2000,'idol'=>1850,'bird'=>1700,'snake'=>1550,'gem'=>1150,'torch'=>850,'wild'=>$isFree?180:85,'scatter'=>66];
    $pay=[
        'mask'=>[6=>8.5],
        'idol'=>[6=>9.5],
        'bird'=>[6=>11],
        'snake'=>[6=>14],
        'gem'=>[6=>19],
        'torch'=>[6=>26],
    ];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;
    $mult=$isFree?max(1,(int)($state['free_mult']??1)):1;$steps=[];$total=0;
    for($cascade=1;$cascade<=8;$cascade++){
        $ev=hv_ways_eval_min($grid,$c,$r,$pay,'wild',$betK,360,$mult,6,8);
        if($ev['amount']<=0)break;
        $total+=$ev['amount'];
        $grid=ag_collapse($grid,$c,$r,$ev['positions'],$weights);
        $steps[]=['label'=>'ПОЛНЫЙ ПУТЬ ×'.$mult,'win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>'Каскад '.$cascade,'fx'=>'temple-dust'];
        $mult=min($isFree?10:5,$mult+1);
    }
    $sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?4:($sc>=5?13:($sc===4?10:8));$free+=$award;}
    if($free>0)$state['free_mult']=$isFree?$mult:1;else unset($state['free_mult']);
    return ['win'=>$total,'payload'=>[
        'initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,
        'badge'=>$isFree?'СИЛА ХРАМА ×'.max(1,(int)($state['free_mult']??1)):'ТОЛЬКО ПОЛНЫЕ ПУТИ 6/6'
    ]];
}

function hv3_neon(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=5;
    $weights=['cyan'=>2100,'pink'=>2050,'lime'=>1950,'violet'=>1750,'orange'=>1450,'wild'=>$isFree?175:80,'scatter'=>62];
    $pay=[
        'cyan'=>[5=>4.0],
        'pink'=>[5=>4.4],
        'lime'=>[5=>5.0],
        'violet'=>[5=>6.2],
        'orange'=>[5=>7.8],
    ];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;
    $voltage=$isFree?max(1,(int)($state['voltage']??1)):1;$steps=[];$total=0;
    for($t=1;$t<=8;$t++){
        $ev=hv_ways_eval_min($grid,$c,$r,$pay,'wild',$betK,500,$voltage,5,9);
        if($ev['amount']<=0)break;
        $total+=$ev['amount'];
        $grid=ag_collapse($grid,$c,$r,$ev['positions'],$weights);
        $steps[]=['label'=>'ПОЛНЫЙ ИМПУЛЬС ×'.$voltage,'win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>'Tumble '.$t,'fx'=>'neon-surge'];
        $voltage=min($isFree?10:4,$voltage+1);
    }
    $sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?13:($sc===4?9:7));$free+=$award;if(!$isFree)$state['voltage']=1;}
    if($free>0)$state['voltage']=$isFree?$voltage:($state['voltage']??1);else unset($state['voltage']);
    return ['win'=>$total,'payload'=>[
        'initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,
        'badge'=>$isFree?'НАПРЯЖЕНИЕ ×'.max(1,(int)($state['voltage']??1)):'5/5 БАРАБАНОВ • ПЛОТНОСТЬ 9+'
    ]];
}
