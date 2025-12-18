<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Container\Container;

class RouteLoader
{
    public function __construct(
        private Router $router,
        private Container $container
    ) {}

    /**
     * Load routes from a configuration file
     * 
     * @param string $path Path to the routes configuration file
     * @return void
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Routes file not found: {$path}");
        }

        $routeDefinition = require $path;

        if (!is_callable($routeDefinition)) {
            throw new \RuntimeException("Routes file must return a callable");
        }

        // Execute the route definition callback with the router
        // The callback will receive the router (which may be a VersionedRouter)
        $routeDefinition($this->router);
    }

    /**
     * Load routes from multiple configuration files
     * 
     * @param array $paths Array of paths to route configuration files
     * @return void
     */
    public function loadMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            $this->load($path);
        }
    }

    /**
     * Load routes from a directory
     * 
     * @param string $directory Directory containing route files
     * @param string $pattern File pattern to match (default: *.php)
     * @return void
     */
    public function loadFromDirectory(string $directory, string $pattern = '*.php'): void
    {
        if (!is_dir($directory)) {
            throw new \RuntimeException("Routes directory not found: {$directory}");
        }

        $files = glob($directory . '/' . $pattern);

        if ($files === false) {
            throw new \RuntimeException("Failed to read routes directory: {$directory}");
        }

        foreach ($files as $file) {
            $this->load($file);
        }
    }

    /**
     * Get the router instance
     * 
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
