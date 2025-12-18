<?php

declare(strict_types=1);

namespace Infrastructure\Auth;

class TokenBlacklist
{
    private array $storage = [];
    private ?object $redis = null;

    public function __construct(?object $redis = null)
    {
        $this->redis = $redis;
    }

    public function revoke(string $token, int $ttl): void
    {
        $key = $this->getKey($token);
        
        if ($this->redis) {
            // Use Redis if available
            $this->redis->setex($key, $ttl, '1');
        } else {
            // Fallback to in-memory storage
            $this->storage[$key] = time() + $ttl;
        }
    }

    public function isRevoked(string $token): bool
    {
        $key = $this->getKey($token);
        
        if ($this->redis) {
            return (bool) $this->redis->exists($key);
        }
        
        // Check in-memory storage
        if (isset($this->storage[$key])) {
            if ($this->storage[$key] > time()) {
                return true;
            }
            // Clean up expired entry
            unset($this->storage[$key]);
        }
        
        return false;
    }

    private function getKey(string $token): string
    {
        return 'token:blacklist:' . hash('sha256', $token);
    }
}
