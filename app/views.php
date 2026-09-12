<?php
declare(strict_types=1);

function site_head(string $title): void {
    $u=current_user();$runtime=['loggedIn'=>(bool)$u,'controls'=>$u?cc_controls((int)$u['id']):null,'csrf'=>csrf_token()];
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#110d19"><meta name="color-scheme" content="dark"><meta name="mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-capable" content="yes"><title>'.e($title).'</title><link rel="stylesheet" href="'.asset_url('site.css').'"><link rel="stylesheet" href="'.asset_url('library.css').'"><link rel="stylesheet" href="'.asset_url('ecosystem.css').'"><link rel="stylesheet" href="'.asset_url('lobby-v2.css').'"><link rel="stylesheet" href="'.asset_url('release-polish.css').'"><script>window.CC_RUNTIME='.json_encode($runtime,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).';</script><script defer src="'.asset_url('site-runtime.js').'"></script></head>';
}
function site_nav(?array $user): void {
    echo '<nav class="cc-nav"><div class="cc-container cc-nav-inner"><a class="cc-logo" href="index.php">Candy<span>Club</span></a><div class="cc-nav-links"><a href="index.php#slots">Слоты</a><a href="index.php#mini">Мини-игры</a>';
    if($user)echo '<a href="club.php">Клуб</a><a href="account.php">Профиль</a>';if($user&&is_admin($user))echo '<a href="admin.php">Админ</a>';
    echo '</div><div class="cc-nav-actions">';
    if($user){echo '<a class="cc-balance" href="account.php"><small>Виртуальный баланс</small><strong>'.e(money_rub((int)$user['balance_kopecks'])).'</strong></a>';echo '<a class="cc-btn cc-btn-ghost cc-user-chip" href="club.php">'.e($user['username']).'</a>';echo '<form action="logout.php" method="post" class="cc-logout"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><button class="cc-btn cc-btn-ghost" type="submit">Выйти</button></form>';}else{echo '<a class="cc-btn cc-btn-ghost" href="login.php">Войти</a><a class="cc-btn cc-btn-primary" href="register.php">Регистрация</a>';}
    echo '</div></div></nav>';
}
function site_footer(): void {
    $u=current_user();echo '<footer class="cc-footer"><div class="cc-container cc-footer-grid"><div><strong>CandyClub</strong><p>Оригинальная развлекательная платформа с серверными слотами и мини-играми.</p></div><div><span>18+ • Все балансы, ставки и выигрыши в этой версии виртуальные и не имеют денежной ценности.</span>'.($u?'<p><a href="club.php#playControls">Лимиты и игровая пауза</a></p>':'').'</div></div></footer></body></html>';
}
