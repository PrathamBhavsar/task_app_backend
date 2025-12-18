<?php

declare(strict_types=1);

namespace Framework\Cache;

use Redis;

class RedisStore implements CacheStoreInterface
{
    private Redis $redis;
    private string $prefix;

    public function __construct(Redis $redis, string $prefix = 'cache')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        $key = $this->prefix($key);
        $value = serialize($value);
        
        return $this->redis->setex($key, $ttl, $value);
    }

    public function get(string $key): mixed
    {
        $key = $this->prefix($key);
        $value = $this->redis->get($key);
        
        if ($value === false) {
            return null;
        }
        
        return unserialize($value);
    }

    public function has(string $key): bool
    {
        $key = $this->prefix($key);
        return $this->redis->exists($key) > 0;
    }

    public function forget(string $key): bool
    {
        $key = $this->prefix($key);
        return $this->redis->del($key) > 0;
    }

    public function flush(): bool
    {
        // Only flush keys with our prefix
        $pattern = $this->prefix('*');
        $keys = $this->redis->keys($pattern);
        
        if (empty($keys)) {
            return true;
        }
        
        return $this->redis->del($keys) > 0;
    }

    public function ttl(string $key): ?int
    {
        $key = $this->prefix($key);
        $ttl = $this->redis->ttl($key);
        
        if ($ttl < 0) {
            return null;
        }
        
        return $ttl;
    }

    private function prefix(string $key): string
    {
        return $this->prefix . ':' . $key;
    }
}
