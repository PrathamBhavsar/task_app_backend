<?php

declare(strict_types=1);

namespace Framework\Queue;

use Redis;
use Framework\Config\Config;

class QueueFactory
{
    public static function create(Config $config): QueueManager
    {
        $driver = $config->getString('queue.driver', 'redis');

        // Check if Redis extension is available
        if ($driver === 'redis' && !extension_loaded('redis')) {
            error_log('Redis extension not available, falling back to array storage');
            return new QueueManager(null);
        }

        return match ($driver) {
            'redis' => self::createRedisQueue($config),
            'array' => new QueueManager(null),
            default => new QueueManager(null),
        };
    }

    private static function createRedisQueue(Config $config): QueueManager
    {
        $redis = new Redis();
        
        $host = $config->getString('queue.redis.host', 'localhost');
        $port = $config->getInt('queue.redis.port', 6379);
        $password = $config->getString('queue.redis.password', null);
        $database = $config->getInt('queue.redis.database', 1);
        
        $redis->connect($host, $port);
        
        if ($password !== null) {
            $redis->auth($password);
        }
        
        $redis->select($database);
        
        return new QueueManager($redis);
    }
}
