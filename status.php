<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');header('X-Content-Type-Options: nosniff');
$ok=false;$db=false;$schema=false;$migrations=false;$configFile=__DIR__.'/app/config.php';
try{
    if(!is_file($configFile)||!extension_loaded('pdo_mysql'))throw new RuntimeException('not-ready');$cfg=require $configFile;$d=$cfg['db']??[];$dsn=sprintf('mysql:host=%s;dbname=%s;charset=%s',$d['host']??'localhost',$d['name']??'',$d['charset']??'utf8mb4');$pdo=new PDO($dsn,$d['user']??'',$d['pass']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>2]);$pdo->query('SELECT 1')->fetchColumn();$db=true;$tables=$pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);$schema=!array_diff(['users','wallet_transactions','user_game_states','game_rounds'],$tables);if(is_file(__DIR__.'/app/migrations.php')){require_once __DIR__.'/app/migrations.php';$known=array_keys(cc_migrations());$applied=in_array('schema_migrations',$tables,true)?array_map('strval',$pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN)):[];$migrations=!array_diff($known,$applied);}$ok=$db&&$schema&&$migrations;
}catch(Throwable $e){$ok=false;}
http_response_code($ok?200:503);echo json_encode(['ok'=>$ok,'database'=>$db,'schema'=>$schema,'migrations'=>$migrations,'checked_at'=>gmdate('c')],JSON_UNESCAPED_SLASHES);
