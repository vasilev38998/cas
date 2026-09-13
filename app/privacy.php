<?php
declare(strict_types=1);

function cc_sanitize_game_state_for_export(array $state): array {
    $key=(string)($state['game_key']??'');$raw=json_decode((string)($state['multiplier_map_json']??'{}'),true);if(!is_array($raw))$raw=[];
    if($key==='mini-mines'){
        $safe=['active'=>!empty($raw['round']),'round'=>$raw['round']??null,'revealed'=>array_values(array_map('intval',is_array($raw['revealed']??null)?$raw['revealed']:[])),'bet_kopecks'=>isset($raw['betK'])?(int)$raw['betK']:null,'mines'=>isset($raw['mines'])?(int)$raw['mines']:null,'started_at_unix'=>isset($raw['started'])?(int)$raw['started']:null];
        $state['multiplier_map_json']=json_encode($safe,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }elseif($key==='mini-tower'){
        $history=[];foreach(is_array($raw['history']??null)?$raw['history']:[] as $h)if(is_array($h))$history[]=['floor'=>(int)($h['floor']??0),'choice'=>(int)($h['choice']??-1),'trap'=>(int)($h['trap']??-1),'safe'=>!empty($h['safe'])];
        $safe=['active'=>!empty($raw['round']),'round'=>$raw['round']??null,'level'=>(int)($raw['level']??0),'bet_kopecks'=>isset($raw['betK'])?(int)$raw['betK']:null,'history'=>$history,'started_at_unix'=>isset($raw['started'])?(int)$raw['started']:null];
        $state['multiplier_map_json']=json_encode($safe,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    return $state;
}

function cc_sanitize_game_states_for_export(array $states): array {foreach($states as $i=>$state)if(is_array($state))$states[$i]=cc_sanitize_game_state_for_export($state);return $states;}
