<?php
declare(strict_types=1);
require_once __DIR__.'/extra_games.php';

function hv_lines8_4(): array {
    return [[0,0,0,0,0],[1,1,1,1,1],[2,2,2,2,2],[3,3,3,3,3],[0,1,2,1,0],[3,2,1,2,3],[1,0,0,0,1],[2,3,3,3,2]];
}
function hv_lines6_3(): array {
    return [[1,1,1,1,1],[0,0,0,0,0],[2,2,2,2,2],[0,1,2,1,0],[2,1,0,1,2],[1,0,1,2,1]];
}
function hv_clusters(array $grid,int $cols,int $rows,int $min=7): array {
    $seen=[];$out=[];
    for($i=0;$i<$cols*$rows;$i++){
        if(isset($seen[$i])||in_array($grid[$i],['bomb','scatter'],true))continue;
        $sym=$grid[$i];$stack=[$i];$cells=[];$seen[$i]=true;
        while($stack){
            $cur=array_pop($stack);$cells[]=$cur;
            foreach(ag_neighbors($cur,$cols,$rows) as $j){
                if(!isset($seen[$j])&&$grid[$j]===$sym){$seen[$j]=true;$stack[]=$j;}
            }
        }
        if(count($cells)>=$min)$out[]=['symbol'=>$sym,'cells'=>$cells];
    }
    return $out;
}
function hv_cluster_factor(int $n): float {
    return $n>=20?5.5:($n>=16?3.0:($n>=12?1.5:($n>=9?.72:($n>=7?.38:.22))));
}
function hv_ways_eval_min(array $grid,int $cols,int $rows,array $pay,string $wild,int $betK,int $divisor,int $mult,int $minReels,int $minMatches): array {
    $total=0;$positions=[];$detail=[];
    foreach($pay as $sym=>$table){
        $counts=[];$reelPositions=[];$reels=0;
        for($c=0;$c<$cols;$c++){
            $ps=[];
            for($r=0;$r<$rows;$r++){
                $i=$c*$rows+$r;$s=$grid[$i];
                if($s===$sym||$s===$wild)$ps[]=$i;
            }
            if(!$ps)break;
            $counts[]=count($ps);$reelPositions[]=$ps;$reels++;
        }
        if($reels<$minReels)continue;
        $use=min($reels,max(array_keys($table)));
        while($use>=$minReels&&!isset($table[$use]))$use--;
        if($use<$minReels)continue;
        $matched=0;$ways=1;
        for($i=0;$i<$use;$i++){$matched+=$counts[$i];$ways*=$counts[$i];}
        if($matched<$minMatches)continue;
        $amount=(int)round($betK*$table[$use]*$ways*$mult/$divisor);
        if($amount<=0)continue;
        $total+=$amount;
        for($i=0;$i<$use;$i++)$positions=array_merge($positions,$reelPositions[$i]);
        $detail[]=['symbol'=>$sym,'reels'=>$use,'matches'=>$matched,'ways'=>$ways,'amount'=>$amount/100];
    }
    return ['amount'=>$total,'positions'=>array_values(array_unique($positions)),'wins'=>$detail];
}

function hv_fruit(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=4;
    $weights=['cherry'=>2050,'lemon'=>1950,'grape'=>1750,'bell'=>1350,'seven'=>900,'diamond'=>580,'wild'=>$isFree?250:150,'scatter'=>105];
    $pay=['cherry'=>[3=>1.2,4=>3.6,5=>10],'lemon'=>[3=>1.3,4=>4,5=>11],'grape'=>[3=>1.5,4=>4.8,5=>14],'bell'=>[3=>2.2,4=>7,5=>20],'seven'=>[3=>3.2,4=>11,5=>32],'diamond'=>[3=>5.5,4=>20,5=>60],'wild'=>[3=>7,4=>25,5=>75]];
    $grid=ag_grid($c,$r,$weights);$sticky=$state['sticky']??[];
    if($isFree){
        foreach($sticky as $p)if(isset($grid[$p]))$grid[$p]='wild';
        foreach($grid as $i=>$s)if($s==='wild')$sticky[$i]=$i;
        $sticky=array_values(array_unique($sticky));$state['sticky']=$sticky;
    }
    $ev=ag_line_eval($grid,$c,$r,hv_lines8_4(),$pay,'wild',$betK);$sc=ag_scatter_count($grid);$award=0;
    if($sc>=3){$award=$isFree?4:($sc>=5?14:($sc===4?10:7));$free+=$award;if(!$isFree)$state['sticky']=[];}
    if($free<=0)unset($state['sticky']);
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>'8 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'fx'=>'fruit-pop'];
    return ['win'=>$ev['amount'],'payload'=>['initial_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'ЛИПКИЕ ВАЙЛДЫ':'8 ЛИНИЙ • ВЫСОКАЯ ВОЛАТИЛЬНОСТЬ','sticky'=>array_values($state['sticky']??[])]];
}

function hv_temple(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=6;$r=5;
    $weights=['mask'=>1950,'idol'=>1800,'bird'=>1650,'snake'=>1500,'gem'=>1150,'torch'=>850,'wild'=>$isFree?210:105,'scatter'=>72];
    $pay=['mask'=>[5=>3.5,6=>7.5],'idol'=>[5=>4,6=>8.5],'bird'=>[5=>4.8,6=>10],'snake'=>[5=>6.2,6=>13],'gem'=>[5=>8.5,6=>17],'torch'=>[5=>11,6=>23]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$mult=$isFree?max(1,(int)($state['free_mult']??1)):1;$steps=[];$total=0;
    for($cascade=1;$cascade<=9;$cascade++){
        $ev=hv_ways_eval_min($grid,$c,$r,$pay,'wild',$betK,480,$mult,5,7);
        if($ev['amount']<=0)break;
        $total+=$ev['amount'];$grid=ag_collapse($grid,$c,$r,$ev['positions'],$weights);
        $steps[]=['label'=>'ДЛИННЫЕ ПУТИ ×'.$mult,'win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>'Каскад '.$cascade,'fx'=>'temple-dust'];
        $mult=min($isFree?10:5,$mult+1);
    }
    $sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?4:($sc>=5?13:($sc===4?10:8));$free+=$award;}
    if($free>0)$state['free_mult']=$isFree?$mult:1;else unset($state['free_mult']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'СИЛА ХРАМА ×'.max(1,(int)($state['free_mult']??1)):'5+ БАРАБАНОВ • РЕДКИЕ ПУТИ']];
}

function hv_jungle(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=3;
    $weights=['parrot'=>2050,'tiger'=>1400,'leaf'=>2350,'drum'=>1850,'gem'=>1050,'wild'=>125,'coin'=>360];
    $pay=['parrot'=>[3=>1.3,4=>4,5=>11],'tiger'=>[3=>2.6,4=>8,5=>24],'leaf'=>[3=>1,4=>3,5=>8.5],'drum'=>[3=>1.6,4=>5.2,5=>15],'gem'=>[3=>3.6,4=>12,5=>36],'wild'=>[3=>6.5,4=>23,5=>70]];
    $grid=ag_grid($c,$r,$weights);$ev=ag_line_eval($grid,$c,$r,hv_lines6_3(),$pay,'wild',$betK);$steps=[];$total=$ev['amount'];
    if($ev['amount']>0)$steps[]=['label'=>'6 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'fx'=>'jungle-hit'];
    $coinPos=[];foreach($grid as $i=>$s)if($s==='coin')$coinPos[]=$i;$feature=null;
    if(count($coinPos)>=6){
        $held=[];$pool=[50,75,100,100,125,150,200,300,500,1000];
        foreach($coinPos as $p)$held[$p]=$pool[array_rand($pool)];
        $respins=3;$frames=[];$guard=0;
        while($respins>0&&count($held)<15&&$guard++<35){
            $new=[];
            for($i=0;$i<15;$i++){
                if(isset($held[$i]))continue;
                if(random_int(1,100)<=13){$v=$pool[array_rand($pool)];$held[$i]=$v;$new[$i]=$v;}
            }
            if($new)$respins=3;else$respins--;
            $frames[]=['respins'=>$respins,'coins'=>$held,'new'=>array_keys($new)];
        }
        $featureK=0;foreach($held as $f)$featureK+=(int)round($betK*$f/100);
        $full=count($held)===15;if($full)$featureK+=$betK*75;$total+=$featureK;
        $feature=['type'=>'hold','title'=>'ЗОЛОТОЙ ТОТЕМ','frames'=>$frames,'coins'=>$held,'full'=>$full,'win'=>$featureK/100];
    }
    return ['win'=>$total,'payload'=>['initial_grid'=>$grid,'steps'=>$steps,'feature'=>$feature,'free_spins_awarded'=>0,'badge'=>'6 ЛИНИЙ • РЕДКИЙ HOLD & RESPIN']];
}

function hv_crystal(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=8;$r=8;
    $weights=['ruby'=>2000,'emerald'=>2000,'sapphire'=>2000,'amethyst'=>1950,'sunstone'=>1900,'bomb'=>$isFree?360:145,'scatter'=>58];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$steps=[];$total=0;
    for($cascade=1;$cascade<=10;$cascade++){
        $clusters=hv_clusters($grid,$c,$r,7);if(!$clusters)break;
        $remove=[];$amount=0;
        foreach($clusters as $cl){$remove=array_merge($remove,$cl['cells']);$amount+=(int)round($betK*hv_cluster_factor(count($cl['cells'])));}
        $remove=array_values(array_unique($remove));$bombs=[];
        foreach($remove as $p)foreach(ag_neighbors($p,$c,$r) as $n)if(($grid[$n]??'')==='bomb')$bombs[$n]=true;
        $exploded=[];$bombBonus=0;
        foreach(array_keys($bombs) as $b){
            $bc=intdiv($b,$r);$br=$b%$r;
            for($dc=-1;$dc<=1;$dc++)for($dr=-1;$dr<=1;$dr++){
                $cc=$bc+$dc;$rr=$br+$dr;if($cc>=0&&$cc<$c&&$rr>=0&&$rr<$r)$exploded[]=$cc*$r+$rr;
            }
            $pool=[75,100,125,150,200,300];$bonus=$pool[array_rand($pool)];$bombBonus+=(int)round($betK*$bonus/100);
        }
        $remove=array_values(array_unique(array_merge($remove,$exploded)));$amount+=$bombBonus;$total+=$amount;
        $grid=ag_collapse($grid,$c,$r,$remove,$weights);
        $steps[]=['label'=>$bombs?'ЦЕПНОЙ ВЗРЫВ':'КЛАСТЕР 7+','win'=>$amount/100,'positions'=>$remove,'grid_after'=>$grid,'extra'=>$bombs?('Бомб: '.count($bombs)):'Каскад '.$cascade,'fx'=>$bombs?'crystal-blast':'crystal-pulse'];
    }
    $sc=ag_scatter_count($initial);$award=0;
    if($sc>=4){$award=$isFree?3:($sc>=6?9:($sc===5?7:6));$free+=$award;}
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'РЕАКТОР: БОМБЫ УСИЛЕНЫ':'КЛАСТЕРЫ ТОЛЬКО 7+']];
}

function hv_scroll(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=3;
    $weights=['falcon'=>1650,'scarab'=>2000,'lotus'=>2350,'ankh'=>1800,'crown'=>1050,'wild'=>125,'scatter'=>95];
    $pay=['falcon'=>[3=>2,4=>6,5=>18],'scarab'=>[3=>1.3,4=>4.2,5=>12.5],'lotus'=>[3=>1,4=>3.2,5=>9],'ankh'=>[3=>1.65,4=>5.2,5=>16],'crown'=>[3=>3.2,4=>12,5=>36],'wild'=>[3=>6.5,4=>23,5=>72]];
    $grid=ag_grid($c,$r,$weights);$chosen=$state['chosen']??null;$expanded=[];
    if($isFree&&$chosen){
        for($col=0;$col<$c;$col++){
            $hit=false;for($row=0;$row<$r;$row++)if($grid[$col*$r+$row]===$chosen)$hit=true;
            if($hit){$expanded[]=$col;for($row=0;$row<$r;$row++)$grid[$col*$r+$row]=$chosen;}
        }
    }
    $ev=ag_line_eval($grid,$c,$r,hv_lines6_3(),$pay,'wild',$betK);$sc=ag_scatter_count($grid);$award=0;
    if($sc>=3){
        if($isFree)$award=4;
        else{$award=$sc>=5?14:($sc===4?10:7);$regular=['falcon','scarab','lotus','ankh','crown'];$chosen=$regular[array_rand($regular)];$state['chosen']=$chosen;}
        $free+=$award;
    }
    if($free<=0)unset($state['chosen']);
    $steps=[];if($ev['amount']>0)$steps[]=['label'=>$expanded?'СОЛНЕЧНОЕ РАСШИРЕНИЕ':'6 ЛИНИЙ','win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>$expanded?('Расширено: '.count($expanded)):'','fx'=>$expanded?'sun-expand':'desert-win'];
    return ['win'=>$ev['amount'],'payload'=>['initial_grid'=>$grid,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree&&$chosen?'ОСОБЫЙ СИМВОЛ: '.$chosen:'6 ЛИНИЙ • РЕДКИЙ БОНУС','chosen'=>$chosen,'expanded_reels'=>$expanded]];
}

function hv_neon(int $betK,bool $isFree,int &$free,array &$state): array {
    $c=5;$r=5;
    $weights=['cyan'=>2050,'pink'=>2000,'lime'=>1900,'violet'=>1700,'orange'=>1450,'wild'=>$isFree?205:100,'scatter'=>72];
    $pay=['cyan'=>[5=>3.4],'pink'=>[5=>3.8],'lime'=>[5=>4.4],'violet'=>[5=>5.4],'orange'=>[5=>6.8]];
    $grid=ag_grid($c,$r,$weights);$initial=$grid;$voltage=$isFree?max(1,(int)($state['voltage']??1)):1;$steps=[];$total=0;
    for($t=1;$t<=9;$t++){
        $ev=hv_ways_eval_min($grid,$c,$r,$pay,'wild',$betK,600,$voltage,5,8);
        if($ev['amount']<=0)break;
        $total+=$ev['amount'];$grid=ag_collapse($grid,$c,$r,$ev['positions'],$weights);
        $steps[]=['label'=>'НАПРЯЖЕНИЕ ×'.$voltage,'win'=>$ev['amount']/100,'positions'=>$ev['positions'],'grid_after'=>$grid,'extra'=>'Tumble '.$t,'fx'=>'neon-surge'];
        $voltage=min($isFree?10:4,$voltage+1);
    }
    $sc=ag_scatter_count($initial);$award=0;
    if($sc>=3){$award=$isFree?3:($sc>=5?13:($sc===4?9:7));$free+=$award;if(!$isFree)$state['voltage']=1;}
    if($free>0)$state['voltage']=$isFree?$voltage:($state['voltage']??1);else unset($state['voltage']);
    return ['win'=>$total,'payload'=>['initial_grid'=>$initial,'steps'=>$steps,'feature'=>null,'free_spins_awarded'=>$award,'badge'=>$isFree?'НАПРЯЖЕНИЕ ×'.max(1,(int)($state['voltage']??1)):'5 БАРАБАНОВ • ПЛОТНЫЙ TUMBLE']];
}
