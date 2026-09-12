<?php
require __DIR__.'/app/bootstrap.php';
require __DIR__.'/app/views.php';
if(current_user()){header('Location: index.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    cc_rate_limit('register-ip',6,3600);verify_csrf($_POST['csrf']??null);$username=trim((string)($_POST['username']??''));$email=mb_strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');$adult=!empty($_POST['adult']);
    if(!preg_match('/^[\p{L}\p{N}_-]{3,24}$/u',$username))throw new RuntimeException('Имя: 3–24 символа, буквы, цифры, _ или -.');if(!filter_var($email,FILTER_VALIDATE_EMAIL)||mb_strlen($email)>190)throw new RuntimeException('Введите корректный email.');if(mb_strlen($password)<10||mb_strlen($password)>128||!preg_match('/\p{L}/u',$password)||!preg_match('/\d/u',$password))throw new RuntimeException('Пароль: 10–128 символов, минимум одна буква и одна цифра.');if(!$adult)throw new RuntimeException('Для регистрации необходимо подтвердить возраст 18+.');
    $start=(int)(app_config()['app']['starting_balance_kopecks']??1000000);$start=max(0,min(1000000000,$start));$pdo=db();$pdo->beginTransaction();$stmt=$pdo->prepare('INSERT INTO users(username,email,password_hash,balance_kopecks) VALUES(?,?,?,?)');$stmt->execute([$username,$email,password_hash($password,PASSWORD_DEFAULT),$start]);$uid=(int)$pdo->lastInsertId();wallet_entry($pdo,$uid,'welcome_bonus',$start,$start,'signup',['note'=>'Стартовый виртуальный баланс']);cc_mark_adult_confirmed($pdo,$uid);$pdo->commit();
    session_regenerate_id(true);$_SESSION=['user_id'=>$uid,'csrf'=>bin2hex(random_bytes(24))];header('Location: index.php');exit;
  }catch(PDOException $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$error=$e->getCode()==='23000'?'Такое имя или email уже используются.':'Ошибка базы данных.';}catch(RuntimeException $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();$error=$e->getMessage();}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();error_log($e->__toString());$error='Не удалось создать аккаунт. Попробуйте ещё раз.';}
}
site_head('Регистрация — CandyClub');echo '<body class="cc-site">';site_nav(null);
?>
<main class="cc-auth-wrap"><form class="cc-auth-card" method="post" autocomplete="on">
<h1>Создать аккаунт</h1><p>Прогресс и виртуальный баланс будут храниться на сервере.</p>
<?php if($error): ?><div class="cc-form-error"><?=e($error)?></div><?php endif; ?>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<div class="cc-field"><label>Имя игрока</label><input name="username" maxlength="24" required value="<?=e((string)($_POST['username']??''))?>" autocomplete="username"></div>
<div class="cc-field"><label>Email</label><input type="email" name="email" maxlength="190" required value="<?=e((string)($_POST['email']??''))?>" autocomplete="email"></div>
<div class="cc-field"><label>Пароль</label><input type="password" name="password" minlength="10" maxlength="128" required autocomplete="new-password"><small>Минимум 10 символов, буква и цифра.</small></div>
<label class="cc-check"><input type="checkbox" name="adult" value="1" <?=!empty($_POST['adult'])?'checked':''?> required><span>Мне исполнилось 18 лет. Я понимаю, что текущая версия использует только виртуальный баланс.</span></label>
<button class="cc-btn cc-btn-primary" type="submit">Создать аккаунт</button>
<div class="cc-auth-foot">Уже зарегистрированы? <a href="login.php">Войти</a></div>
</form></main>
<?php site_footer(); ?>
