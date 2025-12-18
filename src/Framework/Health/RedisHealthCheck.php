<?php

declare(strict_types=1);

namespace Framework\Health;

class RedisHealthCheck implements HealthCheckInterface
{
    private ?object $redis;

    public function __construct(?object $redis = null)
    {
        $this->redis = $redis;
    }

    public function check(): CheckResult
    {
        if ($this->redis === null) {
            return new CheckResult(
                healthy: true,
                message: 'Redis is not configured (optional)'
            );
        }

        try {
            $this->redis->ping();
            
            return new CheckResult(
                healthy: true,
                message: 'Redis connection is healthy'
            );
        } catch (\Throwable $e) {
            return new CheckResult(
                healthy: false,
                message: 'Redis connection failed: ' . $e->getMessage()
            );
        }
    }
}
