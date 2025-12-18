<?php

return [
    'driver' => $_ENV['CACHE_DRIVER'] ?? 'redis',
    'prefix' => $_ENV['CACHE_PREFIX'] ?? 'api_cache',
    
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => 0,
    ],
];
