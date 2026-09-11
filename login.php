<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
if(current_user()){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    verify_csrf($_POST['csrf']??null);$login=trim((string)($_POST['login']??''));$password=(string)($_POST['password']??'');
    $stmt=db()->prepare('SELECT id,username,email,password_hash FROM users WHERE email=? OR username=? LIMIT 1');$stmt->execute([mb_strtolower($login),$login]);$u=$stmt->fetch();
    if(!$u||!password_verify($password,$u['password_hash']))throw new RuntimeException('Неверный логин или пароль.');
    session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];$_SESSION['csrf']=bin2hex(random_bytes(24));db()->prepare('UPDATE users SET last_login_at=NOW() WHERE id=?')->execute([(int)$u['id']]);header('Location: index.php');exit;
  }catch(Throwable $e){$error=$e->getMessage();}
}
site_head('Вход — CandyClub');echo '<body class="cc-site">';site_nav(null);
?>
<main class="cc-auth-wrap"><form class="cc-auth-card" method="post" autocomplete="on">
<h1>Войти</h1><p>Продолжите с сохранённым балансом и прогрессом.</p>
<?php if($error): ?><div class="cc-form-error"><?=e($error)?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="cc-field"><label>Email или имя игрока</label><input name="login" required value="<?=e((string)($_POST['login']??''))?>" autocomplete="username"></div>
<div class="cc-field"><label>Пароль</label><input type="password" name="password" required autocomplete="current-password"></div>
<button class="cc-btn cc-btn-primary" type="submit">Войти</button>
<div class="cc-auth-foot">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></div>
</form></main>
<?php site_footer(); ?>
