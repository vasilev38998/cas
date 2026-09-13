<?php
declare(strict_types=1);

function cc_sanitize_game_state_for_export(array $state): array {
    $key=(string)($state['game_key']??'');
    if($key==='mini-mines'){
        $raw=json_decode((string)($state['multiplier_map_json']??'{}'),true);if(!is_array($raw))$raw=[];
        $safe=['active'=>!empty($raw['round']),'round'=>$raw['round']??null,'revealed'=>array_values(array_map('intval',is_array($raw['revealed']??null)?$raw['revealed']:[])),'bet_kopecks'=>isset($raw['betK'])?(int)$raw['betK']:null,'mines'=>isset($raw['mines'])?(int)$raw['mines']:null,'started_at_unix'=>isset($raw['started'])?(int)$raw['started']:null];
        $state['multiplier_map_json']=json_encode($safe,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    return $state;
}

function cc_sanitize_game_states_for_export(array $states): array {
    foreach($states as $i=>$state)if(is_array($state))$states[$i]=cc_sanitize_game_state_for_export($state);
    return $states;
}
