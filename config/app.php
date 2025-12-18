<?php

return [
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'key' => $_ENV['APP_KEY'] ?? '',
    
    'timezone' => 'UTC',
    
    'providers' => [
        // Service providers will be registered here
    ],
];
