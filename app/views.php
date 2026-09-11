<?php
declare(strict_types=1);

function site_head(string $title): void {
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="theme-color" content="#110d19"><title>'.e($title).'</title><link rel="stylesheet" href="site.css"></head>';
}
function site_nav(?array $user): void {
    echo '<nav class="cc-nav"><div class="cc-container cc-nav-inner"><a class="cc-logo" href="index.php">Candy<span>Club</span></a><div class="cc-nav-links"><a href="index.php#games">Игры</a>';
    if ($user) echo '<a href="account.php">Аккаунт</a>';
    echo '</div><div class="cc-nav-actions">';
    if ($user) {
        echo '<a class="cc-balance" href="account.php"><small>Игровой баланс</small><strong>'.e(money_rub((int)$user['balance_kopecks'])).'</strong></a>';
        echo '<a class="cc-btn cc-btn-ghost" href="account.php">'.e($user['username']).'</a>';
        echo '<form action="logout.php" method="post" style="margin:0"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><button class="cc-btn cc-btn-ghost" type="submit">Выйти</button></form>';
    } else {
        echo '<a class="cc-btn cc-btn-ghost" href="login.php">Войти</a><a class="cc-btn cc-btn-primary" href="register.php">Регистрация</a>';
    }
    echo '</div></div></nav>';
}
function site_footer(): void {
    echo '<footer class="cc-footer"><div class="cc-container">CandyClub — развлекательная демо-платформа. Все балансы и выигрыши виртуальные и не имеют денежной ценности.</div></footer></body></html>';
}
