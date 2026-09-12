<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/mini_games.php';

$plinko=[16.0,6.0,3.0,1.8,1.1,.7,.4,.7,1.1,1.8,3.0,6.0,16.0];$ev=0.0;for($k=0;$k<=12;$k++)$ev+=mini_comb(12,$k)/pow(2,12)*$plinko[$k];printf("Plinko theoretical EV: %.4fx\n",$ev);
$wheelMult=[0,.4,.6,.8,1,1.2,1.5,2,3,5];$wheelWeights=[10,17,18,16,13,10,7,4,3,2];$wev=0;$sum=array_sum($wheelWeights);foreach($wheelMult as $i=>$m)$wev+=$m*$wheelWeights[$i]/$sum;printf("Wheel weighted EV: %.4fx\n",$wev);
foreach([1.2,1.5,2,3,5,10] as $target)printf("Crash target x%-4s theoretical EV ~= %.4fx\n",$target,.96);
foreach([3,5,8,10] as $mines){$safe=25-$mines;foreach([1,min(3,$safe),min(5,$safe)] as $opened){$prob=mini_comb($safe,$opened)/mini_comb(25,$opened);$mult=mini_mines_multiplier($mines,$opened);printf("Mines %2d / opened %2d: survival %.4f multiplier %.2f EV %.4f\n",$mines,$opened,$prob,$mult,$prob*$mult);}}
if($ev<.90||$ev>1.02)throw new RuntimeException('Plinko EV outside engineering bounds');if($wev<.88||$wev>1.00)throw new RuntimeException('Wheel EV outside engineering bounds');
echo "INSTANT_MATH_OK\n";
