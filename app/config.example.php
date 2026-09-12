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
        'starting_balance_kopecks' => 1000000,
        // Укажите логин/почту администратора. Можно оставить пустыми.
        'admin_usernames' => ['YOUR_ADMIN_USERNAME'],
        'admin_emails' => [],
    ],
];
