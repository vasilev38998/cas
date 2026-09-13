<?php
declare(strict_types=1);

function cc_contains($haystack,$needle): bool {return $needle===''||strpos((string)$haystack,(string)$needle)!==false;}

$isHttps=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||(($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header('X-Permitted-Cross-Domain-Policies: none');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-origin');
header('Origin-Agent-Cluster: ?1');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'; font-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'");
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
if($isHttps)header('Strict-Transport-Security: max-age=15552000');

$configFile=__DIR__.'/config.php';
if(!is_file($configFile)){
    http_response_code(503);$isJson=cc_contains($_SERVER['HTTP_ACCEPT']??'','application/json')||cc_contains($_SERVER['REQUEST_URI']??'','/api/');
    if($isJson){header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>'Сайт ещё не настроен: создайте app/config.php из app/config.example.php.'],JSON_UNESCAPED_UNICODE);}else{echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Настройка CandyClub</title><style>body{font-family:system-ui;background:#17121f;color:#fff;padding:32px;max-width:820px;margin:auto;line-height:1.55}code{background:#2b2138;padding:3px 7px;border-radius:6px}.box{padding:20px;border:1px solid #443650;border-radius:18px;background:#21182c}</style><div class="box"><h1>Нужна первичная настройка</h1><p>Скопируйте <code>app/config.example.php</code> в <code>app/config.php</code>, укажите данные MySQL и импортируйте <code>schema.sql</code>.</p><p>Для проверки окружения откройте <code>/health.php</code>.</p></div>';}
    exit;
}
$config=require $configFile;if(!is_array($config)){http_response_code(500);exit('Invalid app/config.php');}
$appCfg=$config['app']??[];

ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
session_name((string)($appCfg['session_name']??'candyclub_session'));
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Lax']);
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
$now=time();$idle=max(900,min(604800,(int)($appCfg['session_idle_timeout_seconds']??43200)));$rotate=max(300,min(7200,(int)($appCfg['session_rotate_seconds']??1800)));
if(!empty($_SESSION['user_id'])){
    $last=(int)($_SESSION['last_activity']??$now);if($now-$last>$idle){$_SESSION=[];session_regenerate_id(true);}else{if($now-(int)($_SESSION['last_regenerated']??0)>=$rotate){session_regenerate_id(true);$_SESSION['last_regenerated']=$now;}$_SESSION['last_activity']=$now;}
}

$db=$config['db']??[];$dsn=sprintf('mysql:host=%s;dbname=%s;charset=%s',$db['host']??'localhost',$db['name']??'',$db['charset']??'utf8mb4');
try{$pdo=new PDO($dsn,$db['user']??'',$db['pass']??'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}
catch(Throwable $e){http_response_code(503);if(cc_contains($_SERVER['REQUEST_URI']??'','/api/')){header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>false,'error'=>'Нет подключения к базе данных.'],JSON_UNESCAPED_UNICODE);}else{echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>База данных</title><style>body{font-family:system-ui;background:#17121f;color:#fff;padding:32px;max-width:820px;margin:auto;line-height:1.55}code{background:#2b2138;padding:3px 7px;border-radius:6px}.box{padding:20px;border:1px solid #443650;border-radius:18px;background:#21182c}</style><div class="box"><h1>Нет подключения к MySQL</h1><p>Проверьте параметры в <code>app/config.php</code> и убедитесь, что <code>schema.sql</code> импортирован.</p><p>Для проверки окружения откройте <code>/health.php</code>.</p></div>';}exit;}

function db(): PDO {global $pdo;return $pdo;}
function app_config(): array {global $config;return $config;}
function e(string $value): string {return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function asset_url(string $path): string {$clean=ltrim($path,'/');$file=dirname(__DIR__).'/'.$clean;$version=is_file($file)?(string)filemtime($file):'1';return e($clean.'?v='.rawurlencode($version));}
function csrf_token(): string {if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(24));return $_SESSION['csrf'];}
function verify_csrf(?string $token): void {if(!$token||!hash_equals($_SESSION['csrf']??'',$token)){http_response_code(419);throw new RuntimeException('Сессия формы устарела. Обновите страницу.');}}
function json_input(): array {
    $length=(int)($_SERVER['CONTENT_LENGTH']??0);if($length>65536)throw new RuntimeException('Слишком большой запрос.');
    $raw=file_get_contents('php://input');if(!$raw)return[];$data=json_decode($raw,true);if(!is_array($data)||json_last_error()!==JSON_ERROR_NONE)throw new RuntimeException('Некорректный JSON-запрос.');return$data;
}
function json_response(array $payload,int $status=200): void {http_response_code($status);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
function current_user(bool $refresh=false): ?array {
    static $cached=null,$loaded=false;if($refresh){$cached=null;$loaded=false;}if($loaded)return$cached;$loaded=true;$id=(int)($_SESSION['user_id']??0);if($id<=0)return null;
    $stmt=db()->prepare('SELECT id,username,email,balance_kopecks,created_at,last_login_at FROM users WHERE id=? LIMIT 1');$stmt->execute([$id]);$cached=$stmt->fetch()?:null;if(!$cached)unset($_SESSION['user_id']);return$cached;
}
function require_login(bool $api=false): array {$user=current_user();if($user)return$user;if($api)json_response(['ok'=>false,'error'=>'Требуется авторизация.'],401);header('Location: login.php');exit;}
function is_admin(?array $user=null): bool {$user=$user?:current_user();if(!$user)return false;$app=app_config()['app']??[];$names=$app['admin_usernames']??[];$emails=$app['admin_emails']??[];if(!is_array($names))$names=[];if(!is_array($emails))$emails=[];return in_array((string)$user['username'],$names,true)||in_array((string)$user['email'],$emails,true);}
function require_admin(): array {$user=require_login();if(!is_admin($user)){http_response_code(403);exit('Доступ запрещён.');}return$user;}
function money_rub(int $kopecks): string {return number_format($kopecks/100,2,',',' ').' ₽';}
function wallet_entry(PDO $pdo,int $userId,string $type,int $amount,int $balanceAfter,?string $reference=null,array $meta=[]): void {$stmt=$pdo->prepare('INSERT INTO wallet_transactions(user_id,type,amount_kopecks,balance_after_kopecks,reference,metadata_json) VALUES(?,?,?,?,?,?)');$stmt->execute([$userId,$type,$amount,$balanceAfter,$reference,$meta?json_encode($meta,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null]);}

function cc_rate_limit(string $bucket,int $max,int $windowSeconds): void {
    $max=max(1,$max);$windowSeconds=max(1,$windowSeconds);$ip=(string)($_SERVER['REMOTE_ADDR']??'unknown');$dir=sys_get_temp_dir().'/candyclub-rate';if(!is_dir($dir)&&!@mkdir($dir,0700,true)&&!is_dir($dir))return;
    $file=$dir.'/'.hash('sha256',$bucket.'|'.$ip).'.json';$fh=@fopen($file,'c+');if(!$fh)return;
    try{if(!flock($fh,LOCK_EX))return;$raw=stream_get_contents($fh);$data=$raw?json_decode($raw,true):[];$now=time();$start=(int)($data['start']??0);$count=(int)($data['count']??0);if($start<=0||$now-$start>=$windowSeconds){$start=$now;$count=0;}$count++;ftruncate($fh,0);rewind($fh);fwrite($fh,json_encode(['start'=>$start,'count'=>$count]));fflush($fh);if($count>$max){$wait=max(1,$windowSeconds-($now-$start));throw new RuntimeException('Слишком много запросов. Повторите через '.$wait.' сек.');}}finally{flock($fh,LOCK_UN);fclose($fh);}
}
function cc_request_id(array $data): ?string {$id=(string)($data['request_id']??'');return $id!==''&&preg_match('/^[A-Za-z0-9_-]{8,80}$/',$id)?$id:null;}
function cc_idempotency_get(string $scope,?string $requestId): ?array {if(!$requestId)return null;$all=$_SESSION['idempotency']??[];$row=$all[$scope.':'.$requestId]??null;if(!is_array($row)||time()-(int)($row['at']??0)>600)return null;return is_array($row['response']??null)?$row['response']:null;}
function cc_idempotency_store(string $scope,?string $requestId,array $response): void {if(!$requestId)return;if(!isset($_SESSION['idempotency'])||!is_array($_SESSION['idempotency']))$_SESSION['idempotency']=[];$_SESSION['idempotency'][$scope.':'.$requestId]=['at'=>time(),'response'=>$response];if(count($_SESSION['idempotency'])>12){uasort($_SESSION['idempotency'],fn($a,$b)=>(int)($a['at']??0)<=>(int)($b['at']??0));$_SESSION['idempotency']=array_slice($_SESSION['idempotency'],-12,null,true);}}
function cc_client_request_id(): string {return bin2hex(random_bytes(12));}

function player_progress(int $userId): array {
    $stmt=db()->prepare('SELECT COALESCE(SUM(LEAST(cost_kopecks,GREATEST(bet_kopecks,1)*8)),0) effective_wager,COALESCE(SUM(cost_kopecks),0) wagered,COUNT(*) rounds,COALESCE(MAX(win_kopecks),0) best_win FROM game_rounds WHERE user_id=?');$stmt->execute([$userId]);$r=$stmt->fetch()?:['effective_wager'=>0,'wagered'=>0,'rounds'=>0,'best_win'=>0];
    $rounds=(int)$r['rounds'];$xp=(int)floor(((int)$r['effective_wager'])/2500)+$rounds*2;$level=max(1,1+(int)floor(sqrt($xp/150)));$floor=(int)round(pow($level-1,2)*150);$ceil=(int)round(pow($level,2)*150);$pct=$ceil>$floor?max(0,min(100,($xp-$floor)/($ceil-$floor)*100)):0;
    return['xp'=>$xp,'level'=>$level,'progress'=>$pct,'rounds'=>$rounds,'best_win'=>(int)$r['best_win'],'wagered'=>(int)$r['wagered']];
}

require_once __DIR__.'/club.php';
