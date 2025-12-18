<?php

return [
    // Enable or disable error monitoring
    'enabled' => filter_var($_ENV['ERROR_MONITORING_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),
    
    // Error monitoring service (sentry, bugsnag, raygun, custom)
    'service' => $_ENV['ERROR_MONITORING_SERVICE'] ?? 'sentry',
    
    // Sentry configuration
    'sentry' => [
        'dsn' => $_ENV['SENTRY_DSN'] ?? $_ENV['ERROR_MONITORING_DSN'] ?? null,
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'sample_rate' => (float) ($_ENV['SENTRY_SAMPLE_RATE'] ?? 1.0),
        'traces_sample_rate' => (float) ($_ENV['SENTRY_TRACES_SAMPLE_RATE'] ?? 0.1),
        'send_pii' => filter_var($_ENV['SENTRY_SEND_PII'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'max_breadcrumbs' => (int) ($_ENV['SENTRY_MAX_BREADCRUMBS'] ?? 50),
    ],
    
    // Error reporting levels per environment
    // Determines which error types should be reported to the monitoring service
    'report_levels' => [
        'development' => [
            'report_exceptions' => true,
            'report_errors' => true,
            'report_warnings' => false,
            'report_notices' => false,
            // Exception types to ignore in development
            'ignore_exceptions' => [
                \Framework\Error\NotFoundException::class,
            ],
        ],
        'staging' => [
            'report_exceptions' => true,
            'report_errors' => true,
            'report_warnings' => true,
            'report_notices' => false,
            'ignore_exceptions' => [],
        ],
        'production' => [
            'report_exceptions' => true,
            'report_errors' => true,
            'report_warnings' => true,
            'report_notices' => false,
            // Exception types to ignore in production
            'ignore_exceptions' => [
                \Framework\Error\NotFoundException::class,
                \Framework\Error\ValidationException::class,
            ],
        ],
    ],
    
    // Additional context to capture
    'capture_context' => [
        'request' => true,
        'user' => true,
        'environment' => true,
        'server' => true,
    ],
    
    // Custom tags to add to all error reports
    'tags' => [
        'application' => $_ENV['APP_NAME'] ?? 'api',
        'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
    ],
];
