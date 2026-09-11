<?php
require __DIR__.'/app/bootstrap.php';
if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location: index.php');exit;}
try{verify_csrf($_POST['csrf']??null);}catch(Throwable $e){http_response_code(419);exit('Сессия устарела.');}
$_SESSION=[];
if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain']??'',(bool)$p['secure'],(bool)$p['httponly']);}
session_destroy();header('Location: index.php');exit;
