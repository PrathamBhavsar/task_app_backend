<?php

return [
    'driver' => $_ENV['QUEUE_DRIVER'] ?? 'redis',
    'default' => $_ENV['QUEUE_DEFAULT'] ?? 'default',
    
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => 1,
    ],
];
