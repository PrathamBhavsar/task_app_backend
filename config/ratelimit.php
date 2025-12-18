<?php

return [
    // Enable or disable rate limiting globally
    'enabled' => filter_var($_ENV['RATE_LIMIT_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
    
    // Default rate limit settings
    'max_requests' => (int) ($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 60),
    'window' => (int) ($_ENV['RATE_LIMIT_WINDOW'] ?? 60), // seconds
    
    // Per-route rate limits (override defaults)
    'routes' => [
        // Example: 'POST /api/auth/login' => ['max_requests' => 5, 'window' => 60],
        // Example: 'POST /api/auth/register' => ['max_requests' => 3, 'window' => 300],
    ],
    
    // Per-group rate limits
    'groups' => [
        // Example: 'auth' => ['max_requests' => 10, 'window' => 60],
        // Example: 'api' => ['max_requests' => 100, 'window' => 60],
    ],
    
    // Redis configuration for distributed rate limiting
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => (int) ($_ENV['REDIS_RATELIMIT_DB'] ?? 1),
    ],
    
    // Headers to include in responses
    'headers' => [
        'limit' => 'X-RateLimit-Limit',
        'remaining' => 'X-RateLimit-Remaining',
        'reset' => 'X-RateLimit-Reset',
        'retry_after' => 'Retry-After',
    ],
];
