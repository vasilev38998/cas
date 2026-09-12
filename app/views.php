<?php
declare(strict_types=1);

function site_head(string $title): void {
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#110d19"><meta name="color-scheme" content="dark"><title>'.e($title).'</title><link rel="stylesheet" href="'.asset_url('site.css').'"><link rel="stylesheet" href="'.asset_url('library.css').'"><link rel="stylesheet" href="'.asset_url('ecosystem.css').'"></head>';
}
function site_nav(?array $user): void {
    echo '<nav class="cc-nav"><div class="cc-container cc-nav-inner"><a class="cc-logo" href="index.php">Candy<span>Club</span></a><div class="cc-nav-links"><a href="index.php#slots">Слоты</a><a href="index.php#mini">Мини-игры</a>';
    if ($user) echo '<a href="account.php">Профиль</a>';
    if ($user && is_admin($user)) echo '<a href="admin.php">Админ</a>';
    echo '</div><div class="cc-nav-actions">';
    if ($user) {
        echo '<a class="cc-balance" href="account.php"><small>Виртуальный баланс</small><strong>'.e(money_rub((int)$user['balance_kopecks'])).'</strong></a>';
        echo '<a class="cc-btn cc-btn-ghost cc-user-chip" href="account.php">'.e($user['username']).'</a>';
        echo '<form action="logout.php" method="post" class="cc-logout"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><button class="cc-btn cc-btn-ghost" type="submit">Выйти</button></form>';
    } else {
        echo '<a class="cc-btn cc-btn-ghost" href="login.php">Войти</a><a class="cc-btn cc-btn-primary" href="register.php">Регистрация</a>';
    }
    echo '</div></div></nav>';
}
function site_footer(): void {
    echo '<footer class="cc-footer"><div class="cc-container cc-footer-grid"><div><strong>CandyClub</strong><p>Оригинальная развлекательная платформа с серверными слотами и мини-играми.</p></div><div><span>Все балансы, ставки и выигрыши в этой версии виртуальные и не имеют денежной ценности.</span></div></div></footer></body></html>';
}
