<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/app/bootstrap.php';
require dirname(__DIR__,2).'/app/extra_games.php';
$user=require_login(true);
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'error'=>'Метод не поддерживается.'],405);
try{
    $data=json_input();verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??($data['csrf']??null));
    $game=(string)($data['game']??'');$bet=(int)($data['bet']??100);
    json_response(arcade_game_spin(db(),(int)$user['id'],$game,$bet));
}catch(RuntimeException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'Внутренняя ошибка игры.'],500);}
