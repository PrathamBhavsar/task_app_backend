<?php

declare(strict_types=1);

namespace Framework\Cache;

interface CacheStoreInterface
{
    /**
     * Store an item in the cache
     */
    public function put(string $key, mixed $value, int $ttl = 3600): bool;

    /**
     * Retrieve an item from the cache
     */
    public function get(string $key): mixed;

    /**
     * Check if an item exists in the cache
     */
    public function has(string $key): bool;

    /**
     * Remove an item from the cache
     */
    public function forget(string $key): bool;

    /**
     * Remove all items from the cache
     */
    public function flush(): bool;

    /**
     * Get the remaining time to live for a cached item
     */
    public function ttl(string $key): ?int;
}
