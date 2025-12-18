<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Container\Container;
use Framework\Container\ServiceProvider;
use Framework\Config\Config;

class RoutingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Router as singleton
        $this->container->singleton(Router::class, function (Container $container) {
            return new Router();
        });

        // Register RouteLoader as singleton
        $this->container->singleton(RouteLoader::class, function (Container $container) {
            $router = $container->resolve(Router::class);
            return new RouteLoader($router, $container);
        });
    }

    public function boot(): void
    {
        // Load routes from configuration
        $routeLoader = $this->container->resolve(RouteLoader::class);
        $config = $this->container->resolve(Config::class);

        // Get routes file path from config, default to config/routes.php
        $routesPath = $config->getString('app.routes_path', __DIR__ . '/../../../config/routes.php');

        // Support loading from a single file or directory
        if (is_file($routesPath)) {
            $routeLoader->load($routesPath);
        } elseif (is_dir($routesPath)) {
            $routeLoader->loadFromDirectory($routesPath);
        } else {
            throw new \RuntimeException("Routes path not found: {$routesPath}");
        }
    }
}
