<?php

return [
    // Allowed origins for CORS requests
    // Use '*' for all origins (not recommended for production)
    // Or specify an array of allowed origins
    'allowed_origins' => explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? '*'),
    
    // Allowed HTTP methods
    'allowed_methods' => explode(',', $_ENV['CORS_ALLOWED_METHODS'] ?? 'GET,POST,PUT,PATCH,DELETE,OPTIONS'),
    
    // Allowed headers
    'allowed_headers' => explode(',', $_ENV['CORS_ALLOWED_HEADERS'] ?? 'Content-Type,Authorization,X-Requested-With,Accept,Origin'),
    
    // Exposed headers (headers that the browser can access)
    'exposed_headers' => explode(',', $_ENV['CORS_EXPOSED_HEADERS'] ?? 'Content-Length,X-RateLimit-Limit,X-RateLimit-Remaining,X-RateLimit-Reset'),
    
    // Whether to allow credentials (cookies, authorization headers)
    'allow_credentials' => filter_var($_ENV['CORS_ALLOW_CREDENTIALS'] ?? false, FILTER_VALIDATE_BOOLEAN),
    
    // Max age for preflight cache (in seconds)
    'max_age' => (int) ($_ENV['CORS_MAX_AGE'] ?? 86400), // 24 hours
];
