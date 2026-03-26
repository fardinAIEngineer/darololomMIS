<?php

declare(strict_types=1);

return [
    'app_name' => 'Darol Olom MIS - PHP',
    'base_url' => getenv('APP_BASE_URL') ?: '',
    'timezone' => 'Asia/Kabul',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'name' => getenv('DB_NAME') ?: 'darololom_php',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'pagination' => [
        'default_page_size' => 20,
        'allowed' => [10, 20, 50, 100],
    ],
];
