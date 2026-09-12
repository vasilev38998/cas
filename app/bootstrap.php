<?php
declare(strict_types=1);

function cc_contains($haystack, $needle): bool {
    return $needle === '' || strpos((string)$haystack, (string)$needle) !== false;
}

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    $isJson = cc_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') || cc_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
    if ($isJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Сайт ещё не настроен: создайте app/config.php из app/config.example.php.'], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Настройка CandyClub</title><style>body{font-family:system-ui;background:#17121f;color:#fff;padding:32px;max-width:820px;margin:auto;line-height:1.55}code{background:#2b2138;padding:3px 7px;border-radius:6px}.box{padding:20px;border:1px solid #443650;border-radius:18px;background:#21182c}</style><div class="box"><h1>Нужна первичная настройка</h1><p>PHP уже запускается. Теперь скопируйте <code>app/config.example.php</code> в <code>app/config.php</code>, укажите данные MySQL и импортируйте <code>schema.sql</code>.</p><p>Для проверки окружения откройте <code>/health.php</code>.</p></div>';
    }
    exit;
}

$config = require $configFile;
if (!is_array($config)) {
    http_response_code(500);
    exit('Invalid app/config.php');
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name((string)($config['app']['session_name'] ?? 'candyclub_session'));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$db = $config['db'] ?? [];
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'] ?? 'localhost', $db['name'] ?? '', $db['charset'] ?? 'utf8mb4');
try {
    $pdo = new PDO($dsn, $db['user'] ?? '', $db['pass'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (Throwable $e) {
    http_response_code(503);
    if (cc_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>false,'error'=>'Нет подключения к базе данных.'], JSON_UNESCAPED_UNICODE);
    } else {
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>База данных</title><style>body{font-family:system-ui;background:#17121f;color:#fff;padding:32px;max-width:820px;margin:auto;line-height:1.55}code{background:#2b2138;padding:3px 7px;border-radius:6px}.box{padding:20px;border:1px solid #443650;border-radius:18px;background:#21182c}</style><div class="box"><h1>Нет подключения к MySQL</h1><p>Проверьте параметры в <code>app/config.php</code> и убедитесь, что <code>schema.sql</code> импортирован.</p><p>Для проверки окружения откройте <code>/health.php</code>.</p></div>';
    }
    exit;
}

function db(): PDO { global $pdo; return $pdo; }
function app_config(): array { global $config; return $config; }
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function verify_csrf(?string $token): void {
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        throw new RuntimeException('Сессия формы устарела. Обновите страницу.');
    }
}
function json_input(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
function json_response(array $payload, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function current_user(bool $refresh = false): ?array {
    static $cached = null, $loaded = false;
    if ($refresh) { $cached = null; $loaded = false; }
    if ($loaded) return $cached;
    $loaded = true;
    $id = (int)($_SESSION['user_id'] ?? 0);
    if ($id <= 0) return null;
    $stmt = db()->prepare('SELECT id, username, email, balance_kopecks, created_at, last_login_at FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $cached = $stmt->fetch() ?: null;
    if (!$cached) unset($_SESSION['user_id']);
    return $cached;
}
function require_login(bool $api = false): array {
    $user = current_user();
    if ($user) return $user;
    if ($api) json_response(['ok'=>false,'error'=>'Требуется авторизация.'], 401);
    header('Location: login.php');
    exit;
}
function money_rub(int $kopecks): string {
    return number_format($kopecks / 100, 2, ',', ' ') . ' ₽';
}
function wallet_entry(PDO $pdo, int $userId, string $type, int $amount, int $balanceAfter, ?string $reference = null, array $meta = []): void {
    $stmt = $pdo->prepare('INSERT INTO wallet_transactions (user_id,type,amount_kopecks,balance_after_kopecks,reference,metadata_json) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$userId,$type,$amount,$balanceAfter,$reference,$meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null]);
}
