<?php

declare(strict_types=1);

/**
 * Rate Limiter Test Script
 * 
 * This script demonstrates how to use the rate limiting system.
 * Access this file multiple times rapidly to see rate limiting in action.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\RateLimit\RateLimiter;
use Framework\RateLimit\RateLimiterFactory;
use Framework\Config\Config;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Load rate limit configuration
$rateLimitConfig = require __DIR__ . '/../config/ratelimit.php';
$config = new Config(['ratelimit' => $rateLimitConfig]);

// Create rate limiter
$rateLimiter = RateLimiterFactory::create($rateLimitConfig);

// Configuration
$maxAttempts = $config->getInt('ratelimit.max_requests', 60);
$decaySeconds = $config->getInt('ratelimit.window', 60);

// Get client identifier (IP address)
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$key = 'test:' . $clientIp;

// Check if rate limit exceeded
if ($rateLimiter->tooManyAttempts($key, $maxAttempts)) {
    $retryAfter = $rateLimiter->availableIn($key);
    $resetTime = time() + $retryAfter;
    
    header('HTTP/1.1 429 Too Many Requests');
    header('Content-Type: application/json');
    header('X-RateLimit-Limit: ' . $maxAttempts);
    header('X-RateLimit-Remaining: 0');
    header('X-RateLimit-Reset: ' . $resetTime);
    header('Retry-After: ' . $retryAfter);
    
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Too Many Requests',
            'code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $retryAfter,
        ],
    ], JSON_PRETTY_PRINT);
    
    exit;
}

// Increment the counter
$attempts = $rateLimiter->hit($key, $decaySeconds);
$remaining = $rateLimiter->remaining($key, $maxAttempts);
$resetTime = time() + $decaySeconds;

// Set rate limit headers
header('Content-Type: application/json');
header('X-RateLimit-Limit: ' . $maxAttempts);
header('X-RateLimit-Remaining: ' . $remaining);
header('X-RateLimit-Reset: ' . $resetTime);

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Rate limiter is working!',
    'data' => [
        'attempts' => $attempts,
        'remaining' => $remaining,
        'max_attempts' => $maxAttempts,
        'window_seconds' => $decaySeconds,
        'reset_at' => date('Y-m-d H:i:s', $resetTime),
    ],
], JSON_PRETTY_PRINT);
