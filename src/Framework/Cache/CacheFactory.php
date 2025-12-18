<?php

declare(strict_types=1);

namespace Framework\Cache;

use Redis;
use Framework\Config\Config;

class CacheFactory
{
    public static function create(Config $config): CacheStoreInterface
    {
        $driver = $config->getString('cache.driver', 'array');

        return match ($driver) {
            'redis' => self::createRedisStore($config),
            'array' => new ArrayStore(),
            default => new ArrayStore(),
        };
    }

    private static function createRedisStore(Config $config): RedisStore
    {
        $redis = new Redis();
        
        $host = $config->getString('cache.redis.host', 'localhost');
        $port = $config->getInt('cache.redis.port', 6379);
        $password = $config->getString('cache.redis.password', null);
        $database = $config->getInt('cache.redis.database', 0);
        
        $redis->connect($host, $port);
        
        if ($password !== null) {
            $redis->auth($password);
        }
        
        $redis->select($database);
        
        $prefix = $config->getString('cache.prefix', 'api_cache');
        
        return new RedisStore($redis, $prefix);
    }
}
