<?php

declare(strict_types=1);

/**
 * Application Bootstrap
 * 
 * This file initializes the Framework layer and sets up the application:
 * - Loads environment configuration
 * - Initializes dependency injection container
 * - Registers service providers
 * - Loads routes from configuration
 * - Sets up global middleware stack
 */

use Framework\Config\Config;
use Framework\Config\EnvLoader;
use Framework\Container\Container;
use Framework\Container\ServiceProvider;
use Framework\Routing\RoutingServiceProvider;
use Framework\Metrics\MetricsServiceProvider;
use Framework\Queue\QueueServiceProvider;
use Framework\Routing\Router;
use Framework\Middleware\MiddlewarePipeline;
use Framework\Middleware\CorsMiddleware;
use Framework\Middleware\MetricsMiddleware;
use Framework\Error\ErrorHandlerMiddleware;
use Framework\Error\ErrorHandler;
use Framework\Error\ErrorMonitorFactory;
use Framework\Metrics\MetricsCollector;
use Framework\Cache\CacheFactory;
use Framework\Cache\CacheManager;
use Framework\RateLimit\RateLimiterFactory;
use Framework\Health\HealthChecker;
use Framework\Health\DatabaseHealthCheck;
use Framework\Health\RedisHealthCheck;

// Autoload dependencies
require_once __DIR__ . '/vendor/autoload.php';

// ============================================
// 1. Load Environment Configuration
// ============================================

$envLoader = new EnvLoader();
$envPath = __DIR__ . '/.env';

if (file_exists($envPath)) {
    $envLoader->load($envPath);
}

// ============================================
// 2. Initialize Configuration
// ============================================

$config = new Config([
    'app' => require __DIR__ . '/config/app.php',
    'database' => require __DIR__ . '/config/database.php',
    'cache' => require __DIR__ . '/config/cache.php',
    'queue' => require __DIR__ . '/config/queue.php',
    'cors' => require __DIR__ . '/config/cors.php',
    'ratelimit' => require __DIR__ . '/config/ratelimit.php',
    'monitoring' => require __DIR__ . '/config/monitoring.php',
]);

// ============================================
// 3. Initialize Dependency Injection Container
// ============================================

$container = new Container();

// Register Config as singleton
$container->instance(Config::class, $config);

// ============================================
// 4. Register Core Services
// ============================================

// Register CacheManager
$container->singleton(CacheManager::class, function (Container $c) {
    $config = $c->resolve(Config::class);
    return CacheFactory::create($config);
});

// Register MetricsCollector
$container->singleton(MetricsCollector::class, function () {
    return new MetricsCollector();
});

// Register ErrorMonitor
$container->singleton(\Framework\Error\ErrorMonitorInterface::class, function (Container $c) {
    $config = $c->resolve(Config::class);
    return ErrorMonitorFactory::create($config->getArray('monitoring', []));
});

// Register ErrorHandler
$container->singleton(ErrorHandler::class, function (Container $c) {
    $config = $c->resolve(Config::class);
    $errorMonitor = $c->resolve(\Framework\Error\ErrorMonitorInterface::class);
    
    return new ErrorHandler(
        debug: $config->getBool('app.debug', false),
        logPath: __DIR__ . '/error_log',
        errorMonitor: $errorMonitor,
        reportLevels: $config->getArray('monitoring.report_levels', []),
        environment: $config->getString('app.env', 'production')
    );
});

// Register RateLimiter
$container->singleton(\Framework\RateLimit\RateLimiter::class, function (Container $c) {
    $config = $c->resolve(Config::class);
    return RateLimiterFactory::create($config);
});

// Register HealthChecker
$container->singleton(HealthChecker::class, function (Container $c) use ($config) {
    $healthChecker = new HealthChecker();
    
    // Add database health check
    try {
        $em = $c->resolve(\Doctrine\ORM\EntityManagerInterface::class);
        $healthChecker->addCheck('database', new DatabaseHealthCheck($em));
    } catch (\Throwable $e) {
        // Database not available, skip check
    }
    
    // Add Redis health check (optional)
    try {
        $cacheConfig = $config->getArray('cache', []);
        if (isset($cacheConfig['driver']) && $cacheConfig['driver'] === 'redis') {
            // Redis would be configured here if available
            $healthChecker->addCheck('redis', new RedisHealthCheck());
        }
    } catch (\Throwable $e) {
        // Redis not configured, skip check
    }
    
    return $healthChecker;
});

// ============================================
// 4.5. Register Database Services
// ============================================

// Register EntityManager
$container->singleton(\Doctrine\ORM\EntityManagerInterface::class, function () {
    return \Infrastructure\Database\EntityManagerFactory::create();
});

// ============================================
// 4.6. Register Repository Bindings
// ============================================

// Bind repository interfaces to their implementations
$container->bind(\Domain\Repository\UserRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\UserRepository::class);
$container->bind(\Domain\Repository\ClientRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\ClientRepository::class);
$container->bind(\Domain\Repository\AuthRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\AuthRepository::class);
$container->bind(\Domain\Repository\BillRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\BillRepository::class);
$container->bind(\Domain\Repository\ConfigRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\ConfigRepository::class);
$container->bind(\Domain\Repository\DesignerRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\DesignerRepository::class);
$container->bind(\Domain\Repository\MeasurementRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\MeasurementRepository::class);
$container->bind(\Domain\Repository\QuoteRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\QuoteRepository::class);
$container->bind(\Domain\Repository\ServiceMasterRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\ServiceMasterRepository::class);
$container->bind(\Domain\Repository\ServiceRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\ServiceRepository::class);
$container->bind(\Domain\Repository\TaskMessageRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\TaskMessageRepository::class);
$container->bind(\Domain\Repository\TaskRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\TaskRepository::class);
$container->bind(\Domain\Repository\TimelineRepositoryInterface::class, \Infrastructure\Persistence\Doctrine\TimelineRepository::class);

// ============================================
// 5. Register Service Providers
// ============================================

$providers = [
    RoutingServiceProvider::class,
    MetricsServiceProvider::class,
    QueueServiceProvider::class,
];

foreach ($providers as $providerClass) {
    /** @var ServiceProvider $provider */
    $provider = new $providerClass($container);
    $provider->register();
}

// Boot service providers
foreach ($providers as $providerClass) {
    /** @var ServiceProvider $provider */
    $provider = new $providerClass($container);
    $provider->boot();
}

// ============================================
// 6. Setup Global Middleware Stack
// ============================================

$pipeline = new MiddlewarePipeline();

// Add middleware in order (outermost to innermost)

// 1. Error Handler (catches all exceptions)
$errorHandler = $container->resolve(ErrorHandler::class);
$pipeline->pipe(new ErrorHandlerMiddleware($errorHandler));

// 2. CORS (handle cross-origin requests)
$corsConfig = $config->getArray('cors', []);
$pipeline->pipe(new CorsMiddleware($corsConfig));

// 3. Metrics (track request metrics)
if ($config->getBool('metrics.enabled', true)) {
    $metricsCollector = $container->resolve(MetricsCollector::class);
    $pipeline->pipe(new MetricsMiddleware($metricsCollector));
}

// ============================================
// 7. Get Router Instance
// ============================================

$router = $container->resolve(Router::class);

// ============================================
// 8. Return Application Components
// ============================================

return [
    'container' => $container,
    'config' => $config,
    'router' => $router,
    'pipeline' => $pipeline,
];
