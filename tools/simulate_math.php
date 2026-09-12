<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/high_volatility_engines.php';
require dirname(__DIR__).'/app/precision_engines.php';
require dirname(__DIR__).'/app/sky_pantheon.php';
require dirname(__DIR__).'/app/feature_slots.php';
require dirname(__DIR__).'/app/sweet_cascade.php';

$spins=(int)($argv[1]??3000);$bet=10000;
$engines=[
    'fruit-fiesta'=>'hv_fruit','temple-ways'=>'hv3_temple','jungle-hold'=>'hv_jungle','crystal-clusters'=>'hv_crystal','sun-scroll'=>'hv_scroll','neon-rush'=>'hv3_neon','sky-pantheon'=>'hv_sky','velvet-curtains'=>'hv_curtains','mystery-vault'=>'hv_mystery'
];
echo "Base-spin simulation: {$spins} rounds per game\n";echo str_repeat('-',82)."\n";
foreach($engines as $name=>$engine){$hits=0;$features=0;$total=0;$max=0;for($i=0;$i<$spins;$i++){$free=0;$state=[];$r=$engine($bet,false,$free,$state);$win=(int)$r['win'];if($win>0)$hits++;if(($r['payload']['free_spins_awarded']??0)>0||!empty($r['payload']['feature']))$features++;$total+=$win;$max=max($max,$win);}printf("%-20s hit=%6.2f%% feature=%5.2f%% avg=%7.3fx max=%8.2fx\n",$name,$hits/$spins*100,$features/$spins*100,$total/$spins/$bet,$max/$bet);}
function sim_sweet(int $bet): int {$grid=sc_random_grid();$mults=array_fill(0,SC_CELLS,0);$total=0;$cascade=0;while(true){$wins=sc_find_clusters($grid);if(!$wins)break;$cascade++;$raw=0;foreach($wins as $w)$raw+=sc_cluster_amount($w,$mults,$bet)['amount'];$total+=(int)round($raw*sc_combo($cascade));$grid=sc_collapse($grid,$wins);if($cascade>=18)break;}return$total;}
$hits=0;$total=0;$max=0;for($i=0;$i<$spins;$i++){$w=sim_sweet($bet);if($w>0)$hits++;$total+=$w;$max=max($max,$w);}printf("%-20s hit=%6.2f%% feature=%5s avg=%7.3fx max=%8.2fx\n",'sweet-cascade',$hits/$spins*100,'n/a',$total/$spins/$bet,$max/$bet);
