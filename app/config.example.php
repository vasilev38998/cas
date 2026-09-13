<?php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'YOUR_DATABASE_NAME',
        'user' => 'YOUR_DATABASE_USER',
        'pass' => 'YOUR_DATABASE_PASSWORD',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Candy Club',
        'base_url' => '',
        'session_name' => 'candyclub_session',
        // Авторизованная сессия завершается после 12 часов бездействия.
        'session_idle_timeout_seconds' => 43200,
        // ID активной сессии ротируется каждые 30 минут.
        'session_rotate_seconds' => 1800,
        'starting_balance_kopecks' => 1000000,
        // Укажите логин/почту администратора. Можно оставить пустыми.
        'admin_usernames' => ['YOUR_ADMIN_USERNAME'],
        'admin_emails' => [],
    ],
];
