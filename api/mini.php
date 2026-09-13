<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/mini_games.php';
require dirname(__DIR__).'/app/mini_expansion.php';
$user=require_login(true);
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');json_response(['ok'=>false,'error'=>'Метод не поддерживается.'],405);}
try{
    $uid=(int)$user['id'];cc_rate_limit('mini:'.$uid,180,60);$d=json_input();verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??($d['csrf']??null));$game=(string)($d['game']??'');$action=(string)($d['action']??'play');$requestId=cc_request_id($d);$scope='mini:'.$game.':'.$action;
    $operation=function() use($uid,$d,$game,$action): array {
        if($game==='plinko'&&$action==='play')return mini_plinko(db(),$uid,(int)($d['bet']??100));
        if($game==='wheel'&&$action==='play')return mini_wheel(db(),$uid,(int)($d['bet']??100));
        if($game==='crash'&&$action==='play')return mini_crash(db(),$uid,(int)($d['bet']??100),(float)($d['target']??2));
        if($game==='dice'&&$action==='play')return mini_dice(db(),$uid,(int)($d['bet']??100),(int)($d['chance']??50));
        if($game==='mines'&&$action==='start')return mini_mines_start(db(),$uid,(int)($d['bet']??100),(int)($d['mines']??5));
        if($game==='mines'&&$action==='reveal')return mini_mines_reveal(db(),$uid,(int)($d['cell']??-1));
        if($game==='mines'&&$action==='cashout')return mini_mines_cashout(db(),$uid);
        if($game==='tower'&&$action==='start')return mini_tower_start(db(),$uid,(int)($d['bet']??100));
        if($game==='tower'&&$action==='reveal')return mini_tower_reveal(db(),$uid,(int)($d['portal']??-1));
        if($game==='tower'&&$action==='cashout')return mini_tower_cashout(db(),$uid);
        if($game==='daily'&&$action==='claim')return daily_reward_claim_v2(db(),$uid);
        throw new RuntimeException('Неизвестная команда мини-игры.');
    };
    $result=cc_idempotent_execute($uid,$scope,$requestId,$operation);json_response($result);
}catch(CcHttpException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],$e->status);}catch(RuntimeException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'Внутренняя ошибка мини-игры.'],500);}
