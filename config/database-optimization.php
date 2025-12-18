<?php

/**
 * Database Query Optimization Configuration
 * 
 * This file contains configuration for database query logging,
 * N+1 detection, and optimization tools.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Query Logging
    |--------------------------------------------------------------------------
    |
    | Enable query logging to track all database queries. This is useful
    | in development to identify performance issues and N+1 query problems.
    | Should be disabled in production for performance.
    |
    */
    'query_logging' => [
        'enabled' => $_ENV['DB_QUERY_LOG_ENABLED'] ?? ($_ENV['APP_ENV'] === 'development'),
        'log_slow_queries' => $_ENV['DB_QUERY_LOG_SLOW'] ?? true,
        'slow_query_threshold' => (int) ($_ENV['DB_QUERY_SLOW_THRESHOLD'] ?? 100), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | N+1 Query Detection
    |--------------------------------------------------------------------------
    |
    | Automatically detect N+1 query patterns where the same query is
    | executed multiple times. This helps identify opportunities for
    | eager loading optimization.
    |
    */
    'n_plus_one_detection' => [
        'enabled' => $_ENV['DB_DETECT_N_PLUS_ONE'] ?? true,
        'threshold' => (int) ($_ENV['DB_N_PLUS_ONE_THRESHOLD'] ?? 10), // Number of duplicate queries to trigger warning
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Count Threshold
    |--------------------------------------------------------------------------
    |
    | Maximum number of queries per request before a warning is logged.
    | Helps identify endpoints that need optimization.
    |
    */
    'query_threshold' => (int) ($_ENV['DB_QUERY_THRESHOLD'] ?? 50),

    /*
    |--------------------------------------------------------------------------
    | Eager Loading Defaults
    |--------------------------------------------------------------------------
    |
    | Default configuration for eager loading optimization.
    |
    */
    'eager_loading' => [
        'batch_size' => 100, // Default batch size for batch fetching
        'default_associations' => [], // Default associations to always eager load
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Headers
    |--------------------------------------------------------------------------
    |
    | Add query statistics to response headers in development mode.
    | Useful for debugging but should be disabled in production.
    |
    */
    'development_headers' => [
        'enabled' => $_ENV['APP_ENV'] === 'development',
        'include_query_count' => true,
        'include_query_time' => true,
        'include_n_plus_one_warnings' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Optimization Suggestions
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic optimization suggestions based on
    | query analysis.
    |
    */
    'suggestions' => [
        'enabled' => true,
        'high_query_count_threshold' => 50,
        'high_average_duration_threshold' => 0.05, // seconds (50ms)
    ],
];
