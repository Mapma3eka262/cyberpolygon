<?php

return [
    'cache' => [
        'enabled' => env('CACHE_ENABLED', true),
        'driver' => env('CACHE_DRIVER', 'redis'),
        'ttl' => [
            'leaderboard' => 60, // секунды
            'team_stats' => 30,
            'system_metrics' => 10,
        ],
    ],
    
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'strict' => false,
        'modes' => [
            'strict' => false,
            'engine' => 'InnoDB',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'pooling' => [
            'enabled' => true,
            'min_connections' => 5,
            'max_connections' => 100,
            'timeout' => 60,
        ],
    ],
    
    'session' => [
        'driver' => env('SESSION_DRIVER', 'database'),
        'encrypt' => false, // Для производительности
        'lifetime' => env('SESSION_LIFETIME', 120),
        'expire_on_close' => false,
    ],
    
    'queue' => [
        'default' => env('QUEUE_CONNECTION', 'redis'),
        'failed' => [
            'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
            'database' => env('DB_CONNECTION', 'mysql'),
            'table' => 'failed_jobs',
        ],
    ],
    
    'monitoring' => [
        'enabled' => env('MONITORING_ENABLED', true),
        'interval' => 30, // секунды
        'log_retention' => 7, // дни
    ],
];