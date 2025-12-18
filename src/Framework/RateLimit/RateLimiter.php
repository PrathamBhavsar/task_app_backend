<?php

declare(strict_types=1);

namespace Framework\RateLimit;

class RateLimiter
{
    private ?object $redis = null;
    private array $storage = [];

    public function __construct(?object $redis = null)
    {
        $this->redis = $redis;
    }

    /**
     * Determine if the given key has been accessed too many times
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        if ($this->attempts($key) >= $maxAttempts) {
            if ($this->redis && $this->redis->exists($key . ':timer')) {
                return true;
            }
            
            if (isset($this->storage[$key . ':timer']) && $this->storage[$key . ':timer'] > time()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Increment the counter for a given key for a given decay time
     */
    public function hit(string $key, int $decaySeconds = 60): int
    {
        $key = $this->cleanRateLimiterKey($key);
        
        if ($this->redis) {
            $this->redis->incr($key);
            $this->redis->expire($key, $decaySeconds);
            
            // Set timer key to track when the window expires
            if (!$this->redis->exists($key . ':timer')) {
                $this->redis->setex($key . ':timer', $decaySeconds, time() + $decaySeconds);
            }
            
            return (int) $this->redis->get($key);
        }
        
        // Fallback to in-memory storage
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = 0;
            $this->storage[$key . ':timer'] = time() + $decaySeconds;
        }
        
        $this->storage[$key]++;
        
        return $this->storage[$key];
    }

    /**
     * Get the number of attempts for the given key
     */
    public function attempts(string $key): int
    {
        $key = $this->cleanRateLimiterKey($key);
        
        if ($this->redis) {
            return (int) $this->redis->get($key);
        }
        
        return $this->storage[$key] ?? 0;
    }

    /**
     * Get the number of remaining attempts for the given key
     */
    public function remaining(string $key, int $maxAttempts): int
    {
        $attempts = $this->attempts($key);
        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Get the number of seconds until the rate limiter is available again
     */
    public function availableIn(string $key): int
    {
        $key = $this->cleanRateLimiterKey($key);
        
        if ($this->redis) {
            $resetTime = $this->redis->get($key . ':timer');
            if ($resetTime) {
                return max(0, (int) $resetTime - time());
            }
            
            // Fallback to TTL
            $ttl = $this->redis->ttl($key);
            return max(0, $ttl);
        }
        
        // In-memory storage
        if (isset($this->storage[$key . ':timer'])) {
            return max(0, $this->storage[$key . ':timer'] - time());
        }
        
        return 0;
    }

    /**
     * Reset the rate limiter for the given key
     */
    public function resetAttempts(string $key): void
    {
        $key = $this->cleanRateLimiterKey($key);
        
        if ($this->redis) {
            $this->redis->del($key);
            $this->redis->del($key . ':timer');
        } else {
            unset($this->storage[$key]);
            unset($this->storage[$key . ':timer']);
        }
    }

    /**
     * Clear all rate limiters
     */
    public function clear(): void
    {
        if ($this->redis) {
            $keys = $this->redis->keys('rate_limit:*');
            if (!empty($keys)) {
                $this->redis->del(...$keys);
            }
        } else {
            $this->storage = [];
        }
    }

    /**
     * Clean the rate limiter key
     */
    private function cleanRateLimiterKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }
}
