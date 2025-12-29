<?php

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'quidque',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    
    'site' => [
        'name' => 'Quidque Studio',
        'url' => 'http://quidque.local',
        'debug' => true,
    ],
    
    'session' => [
        'lifetime' => 60 * 60 * 24 * 14,
        'cookie_name' => 'quidque_session',
    ],
    
    'auth' => [
        'token_expiry' => 60 * 15,
        'token_cooldown' => 60 * 2,
    ],
    
    'uploads' => [
        'max_size' => 2 * 1024 * 1024,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    ],
];