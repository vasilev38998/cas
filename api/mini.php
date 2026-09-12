<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/mini_games.php';
$user=require_login(true);
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'error'=>'Метод не поддерживается.'],405);
try{
    $d=json_input();verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??($d['csrf']??null));
    $game=(string)($d['game']??'');$action=(string)($d['action']??'play');$uid=(int)$user['id'];
    if($game==='plinko'&&$action==='play')json_response(mini_plinko(db(),$uid,(int)($d['bet']??100)));
    if($game==='wheel'&&$action==='play')json_response(mini_wheel(db(),$uid,(int)($d['bet']??100)));
    if($game==='mines'&&$action==='start')json_response(mini_mines_start(db(),$uid,(int)($d['bet']??100),(int)($d['mines']??5)));
    if($game==='mines'&&$action==='reveal')json_response(mini_mines_reveal(db(),$uid,(int)($d['cell']??-1)));
    if($game==='mines'&&$action==='cashout')json_response(mini_mines_cashout(db(),$uid));
    if($game==='daily'&&$action==='claim')json_response(daily_reward_claim(db(),$uid));
    throw new RuntimeException('Неизвестная команда мини-игры.');
}catch(RuntimeException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'Внутренняя ошибка мини-игры.'],500);}
