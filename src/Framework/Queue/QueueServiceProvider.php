<?php

declare(strict_types=1);

namespace Framework\Queue;

use Framework\Container\Container;
use Framework\Container\ServiceProvider;
use Framework\Config\Config;

class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(QueueManager::class, function (Container $container) {
            $config = $container->resolve(Config::class);
            return QueueFactory::create($config);
        });

        $this->container->singleton(Worker::class, function (Container $container) {
            $queueManager = $container->resolve(QueueManager::class);
            return new Worker($queueManager);
        });
    }
}
