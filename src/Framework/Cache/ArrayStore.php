<?php

declare(strict_types=1);

namespace Framework\Cache;

class ArrayStore implements CacheStoreInterface
{
    private array $storage = [];
    private array $expiry = [];

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        $this->storage[$key] = $value;
        $this->expiry[$key] = time() + $ttl;
        
        return true;
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }
        
        return $this->storage[$key];
    }

    public function has(string $key): bool
    {
        if (!isset($this->storage[$key])) {
            return false;
        }
        
        // Check if expired
        if (isset($this->expiry[$key]) && $this->expiry[$key] < time()) {
            unset($this->storage[$key], $this->expiry[$key]);
            return false;
        }
        
        return true;
    }

    public function forget(string $key): bool
    {
        unset($this->storage[$key], $this->expiry[$key]);
        return true;
    }

    public function flush(): bool
    {
        $this->storage = [];
        $this->expiry = [];
        return true;
    }

    public function ttl(string $key): ?int
    {
        if (!isset($this->expiry[$key])) {
            return null;
        }
        
        $remaining = $this->expiry[$key] - time();
        
        return $remaining > 0 ? $remaining : null;
    }
}
