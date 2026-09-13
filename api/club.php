<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
$user=require_login(true);
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Allow: POST');json_response(['ok'=>false,'error'=>'Метод не поддерживается.'],405);}
try{
    cc_rate_limit('club:'.(int)$user['id'],120,60);$data=json_input();verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN']??($data['csrf']??null));$action=(string)($data['action']??'');
    if($action==='favorite_toggle')json_response(['ok'=>true,'favorites'=>cc_toggle_favorite((int)$user['id'],(string)($data['game']??''))]);
    if($action==='claim_mission')json_response(['ok'=>true]+cc_claim_mission((int)$user['id'],(string)($data['mission']??'')));
    if($action==='set_controls'){$controls=cc_set_controls((int)$user['id'],(int)($data['max_bet']??0),(int)($data['reminder']??60),(int)($data['pause_hours']??0));json_response(['ok'=>true,'controls'=>$controls]);}
    throw new RuntimeException('Неизвестная команда клуба.');
}catch(CcHttpException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],$e->status);}catch(RuntimeException $e){json_response(['ok'=>false,'error'=>$e->getMessage()],400);}catch(Throwable $e){error_log($e->__toString());json_response(['ok'=>false,'error'=>'Внутренняя ошибка.'],500);}
