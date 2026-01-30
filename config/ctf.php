<?php

return [
    'scoring' => [
        'flag1_points' => 30,
        'flag2_points' => 70,
        'wrong_attempt_penalty' => 3,
        'time_bonus_multiplier' => 1.1, // Бонус за быстрое выполнение
    ],
    
    'timing' => [
        'default_task_duration' => 60, // минуты
        'max_task_duration' => 180,
        'min_task_duration' => 5,
        'check_interval' => 60, // секунды, интервал проверки таймеров
    ],
    
    'flags' => [
        'hash_algorithm' => 'sha256',
        'salt' => env('FLAG_SALT', 'ctf-platform-salt-2024'),
        'min_length' => 10,
        'max_length' => 100,
    ],
    
    'teams' => [
        'max_members' => 5,
        'min_members' => 1,
        'default_invite_expiry' => 24, // часы
    ],
    
    'performance' => [
        'cache_ttl' => 300, // секунды
        'session_lifetime' => 120, // минуты
        'max_concurrent_requests' => 100,
    ],
    
    'admin' => [
        'default_username' => 'admin',
        'default_password' => 'admin',
    ],
    
    'competition' => [
        'start_date' => env('COMPETITION_START', '2024-06-01 10:00:00'),
        'end_date' => env('COMPETITION_END', '2024-06-01 18:00:00'),
        'registration_open' => true,
    ],
];