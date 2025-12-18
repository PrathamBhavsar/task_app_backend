<?php

declare(strict_types=1);

namespace Framework\RateLimit;

class RateLimiterFactory
{
    /**
     * Create a RateLimiter instance with Redis connection
     */
    public static function create(array $config): RateLimiter
    {
        $redis = null;
        
        if (extension_loaded('redis')) {
            try {
                $redis = new \Redis();
                $redis->connect(
                    $config['redis']['host'] ?? 'localhost',
                    $config['redis']['port'] ?? 6379
                );
                
                if (!empty($config['redis']['password'])) {
                    $redis->auth($config['redis']['password']);
                }
                
                $redis->select($config['redis']['database'] ?? 1);
            } catch (\Exception $e) {
                // Fall back to in-memory storage if Redis connection fails
                error_log("Redis connection failed for rate limiter: " . $e->getMessage());
                $redis = null;
            }
        }
        
        return new RateLimiter($redis);
    }
}
