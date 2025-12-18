<?php

declare(strict_types=1);

/**
 * Test file for the centralized route registry
 * 
 * This demonstrates:
 * - Loading routes from configuration files
 * - Route grouping by resource
 * - Named routes for URL generation
 * - Middleware application to route groups
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Container\Container;
use Framework\Routing\Router;
use Framework\Routing\RouteLoader;
use Framework\Routing\RoutingServiceProvider;
use Framework\Config\Config;
use Framework\Config\EnvLoader;

// Initialize container
$container = new Container();

// Load environment configuration
$envLoader = new EnvLoader();
$envLoader->load(__DIR__ . '/../.env');

// Register Config service
$container->singleton(Config::class, function () {
    return new Config();
});

// Register and boot RoutingServiceProvider
$routingProvider = new RoutingServiceProvider($container);
$routingProvider->register();
$routingProvider->boot();

// Get router instance
$router = $container->resolve(Router::class);

echo "=== Route Registry Test ===\n\n";

// Test 1: List all registered routes
echo "1. Registered Routes:\n";
echo "-------------------\n";
$routes = $router->getRoutes();
echo "Total routes: " . count($routes) . "\n\n";

foreach ($routes as $route) {
    $name = $route->name ? " (name: {$route->name})" : "";
    $middleware = !empty($route->middleware) ? " [" . implode(', ', array_map(fn($m) => basename(str_replace('\\', '/', $m)), $route->middleware)) . "]" : "";
    echo sprintf("%-6s %-40s => %s%s%s\n", 
        $route->method, 
        $route->pattern, 
        $route->handler,
        $middleware,
        $name
    );
}

echo "\n";

// Test 2: Named route URL generation
echo "2. Named Route URL Generation:\n";
echo "------------------------------\n";

$testRoutes = [
    ['name' => 'health.check', 'params' => [], 'expected' => '/health'],
    ['name' => 'auth.login', 'params' => [], 'expected' => '/api/auth/login'],
    ['name' => 'clients.show', 'params' => ['id' => 123], 'expected' => '/api/clients/123'],
    ['name' => 'projects.update', 'params' => ['id' => 456], 'expected' => '/api/projects/456'],
    ['name' => 'tasks.updateStatus', 'params' => ['id' => 789], 'expected' => '/api/tasks/789/status'],
];

foreach ($testRoutes as $test) {
    try {
        $url = $router->url($test['name'], $test['params']);
        $status = $url === $test['expected'] ? '✓' : '✗';
        echo "{$status} {$test['name']}: {$url}\n";
        if ($url !== $test['expected']) {
            echo "   Expected: {$test['expected']}\n";
        }
    } catch (\Exception $e) {
        echo "✗ {$test['name']}: ERROR - {$e->getMessage()}\n";
    }
}

echo "\n";

// Test 3: URL generation with query parameters
echo "3. URL Generation with Query Parameters:\n";
echo "---------------------------------------\n";

try {
    $url = $router->url('clients.index', [], ['page' => 2, 'limit' => 10]);
    echo "✓ clients.index with query params: {$url}\n";
} catch (\Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n";
}

echo "\n";

// Test 4: Check if named routes exist
echo "4. Named Route Existence Check:\n";
echo "-------------------------------\n";

$checkRoutes = ['clients.index', 'auth.login', 'nonexistent.route'];
foreach ($checkRoutes as $routeName) {
    $exists = $router->hasRoute($routeName);
    $status = $exists ? '✓' : '✗';
    echo "{$status} {$routeName}: " . ($exists ? 'exists' : 'not found') . "\n";
}

echo "\n";

// Test 5: Get route details
echo "5. Route Details:\n";
echo "----------------\n";

$route = $router->getRoute('clients.show');
if ($route) {
    echo "Route: clients.show\n";
    echo "  Method: {$route->method}\n";
    echo "  Pattern: {$route->pattern}\n";
    echo "  Handler: {$route->handler}\n";
    echo "  Middleware: " . (empty($route->middleware) ? 'none' : implode(', ', $route->middleware)) . "\n";
} else {
    echo "Route not found\n";
}

echo "\n";

// Test 6: Load routes from directory
echo "6. Loading Routes from Directory:\n";
echo "---------------------------------\n";

try {
    $routeLoader = $container->resolve(RouteLoader::class);
    
    // Create a new router for this test
    $testRouter = new Router();
    $testLoader = new RouteLoader($testRouter, $container);
    
    // Load from directory
    $testLoader->loadFromDirectory(__DIR__ . '/../config/routes');
    
    $routeCount = count($testRouter->getRoutes());
    echo "✓ Successfully loaded {$routeCount} routes from config/routes/ directory\n";
    
    // List route files
    $files = glob(__DIR__ . '/../config/routes/*.php');
    echo "  Files loaded:\n";
    foreach ($files as $file) {
        echo "    - " . basename($file) . "\n";
    }
} catch (\Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n";
}

echo "\n=== Test Complete ===\n";
