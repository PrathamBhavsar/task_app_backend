<?php

declare(strict_types=1);

namespace Framework\Metrics;

use Framework\Container\Container;
use Framework\Container\ServiceProvider;
use Framework\Cache\CacheManager;

/**
 * Service Provider for Metrics Collection System
 */
class MetricsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register MetricsCollector as singleton
        $this->container->singleton(MetricsCollector::class, function () {
            return new MetricsCollector();
        });

        // Register DoctrineSQLLogger
        $this->container->singleton(DoctrineSQLLogger::class, function (Container $c) {
            return new DoctrineSQLLogger($c->resolve(MetricsCollector::class));
        });

        // Register DatabaseMetricsMiddleware
        $this->container->bind(DatabaseMetricsMiddleware::class, function (Container $c) {
            $queryThreshold = (int) ($_ENV['DB_QUERY_THRESHOLD'] ?? 50);
            
            return new DatabaseMetricsMiddleware(
                $c->resolve(MetricsCollector::class),
                $c->resolve(DoctrineSQLLogger::class),
                $queryThreshold
            );
        });
    }

    public function boot(): void
    {
        // Register common business metrics
        $metrics = $this->container->resolve(MetricsCollector::class);
        
        // Example custom metrics registration
        $metrics->registerCustomMetric(
            'business_operations_total',
            'counter',
            'Total number of business operations performed',
            []
        );
        
        $metrics->registerCustomMetric(
            'active_users',
            'gauge',
            'Number of currently active users',
            []
        );
        
        $metrics->registerCustomMetric(
            'transaction_amount',
            'histogram',
            'Transaction amounts in dollars',
            []
        );
    }
}
