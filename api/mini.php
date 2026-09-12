<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/mini_games.php';
$user=require_login(true);
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'error'=>'Метод не поддерживается.'],405);
try{
    $uid=(int)$user['id'];cc_rate_limit('mini:'.$uid,180,60);$d=json_input();verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??($d['csrf']??null));$game=(string)($d['game']??'');$action=(string)($d['action']??'play');$requestId=cc_request_id($d);$scope='mini:'.$uid.':'.$game.':'.$action;if($cached=cc_idempotency_get($scope,$requestId))json_response($cached);
    if($game==='plinko'&&$action==='play')$result=mini_plinko(db(),$uid,(int)($d['bet']??100));
    elseif($game==='wheel'&&$action==='play')$result=mini_wheel(db(),$uid,(int)($d['bet']??100));
    elseif($game==='crash'&&$action==='play')$result=mini_crash(db(),$uid,(int)($d['bet']??100),(float)($d['target']??2));
    elseif($game==='mines'&&$action==='start')$result=mini_mines_start(db(),$uid,(int)($d['bet']??100),(int)($d['mines']??5));
    elseif($game==='mines'&&$action==='reveal')$result=mini_mines_reveal(db(),$uid,(int)($d['cell']??-1));
    elseif($game==='mines'&&$action==='cashout')$result=mini_mines_cashout(db(),$uid);
    elseif($game==='daily'&&$action==='claim')$result=daily_reward_claim(db(),$uid);
    else throw new RuntimeException('Неизвестная команда мини-игры.');
    cc_idempotency_store($scope,$requestId,$result);json_response($result);
}catch(RuntimeException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'Внутренняя ошибка мини-игры.'],500);}
