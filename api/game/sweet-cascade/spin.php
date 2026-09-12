<?php
declare(strict_types=1);
require dirname(__DIR__,3).'/app/bootstrap.php';
require dirname(__DIR__,3).'/app/sweet_cascade.php';
$user=require_login(true);
if($_SERVER['REQUEST_METHOD']!=='POST')json_response(['ok'=>false,'error'=>'Метод не поддерживается.'],405);
try{
    $uid=(int)$user['id'];cc_rate_limit('sweet:'.$uid,150,60);$data=json_input();verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??($data['csrf']??null));$bet=(int)($data['bet']??100);$mode=(string)($data['mode']??'normal');$requestId=cc_request_id($data);$scope='sweet:'.$uid.':'.$mode;if($cached=cc_idempotency_get($scope,$requestId))json_response($cached);$result=sweet_cascade_spin(db(),$uid,$bet,$mode);cc_idempotency_store($scope,$requestId,$result);json_response($result);
}catch(RuntimeException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'Внутренняя ошибка игры.'],500);}
