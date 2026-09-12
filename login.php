<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
if(current_user()){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    cc_rate_limit('login-ip',20,900);verify_csrf($_POST['csrf']??null);$login=trim((string)($_POST['login']??''));$password=(string)($_POST['password']??'');if($login===''||$password==='')throw new RuntimeException('Неверный логин или пароль.');cc_rate_limit('login-id:'.hash('sha256',mb_strtolower($login)),7,900);
    $stmt=db()->prepare('SELECT id,username,email,password_hash FROM users WHERE email=? OR username=? LIMIT 1');$stmt->execute([mb_strtolower($login),$login]);$u=$stmt->fetch();if(!$u||!password_verify($password,$u['password_hash']))throw new RuntimeException('Неверный логин или пароль.');
    if(password_needs_rehash((string)$u['password_hash'],PASSWORD_DEFAULT))db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),(int)$u['id']]);
    session_regenerate_id(true);$_SESSION=['user_id'=>(int)$u['id'],'csrf'=>bin2hex(random_bytes(24))];db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([(int)$u['id']]);header('Location: index.php');exit;
  }catch(RuntimeException $e){$error=$e->getMessage();}catch(Throwable $e){error_log($e->__toString());$error='Не удалось выполнить вход. Попробуйте ещё раз.';}
}
site_head('Вход — CandyClub');echo '<body class="cc-site">';site_nav(null);
?>
<main class="cc-auth-wrap"><form class="cc-auth-card" method="post" autocomplete="on">
<h1>Войти</h1><p>Продолжите с сохранённым виртуальным балансом и прогрессом.</p>
<?php if($error): ?><div class="cc-form-error"><?=e($error)?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="cc-field"><label>Email или имя игрока</label><input name="login" maxlength="190" required value="<?=e((string)($_POST['login']??''))?>" autocomplete="username"></div>
<div class="cc-field"><label>Пароль</label><input type="password" name="password" maxlength="128" required autocomplete="current-password"></div>
<button class="cc-btn cc-btn-primary" type="submit">Войти</button>
<div class="cc-auth-foot">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></div>
</form></main>
<?php site_footer(); ?>
