<?php

declare(strict_types=1);

/**
 * Test file for API versioning support
 * 
 * This demonstrates:
 * - Version-specific route registration
 * - Deprecation warning headers for old versions
 * - Default version fallback
 * - Multiple API versions coexisting
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Container\Container;
use Framework\Routing\VersionedRouter;
use Framework\Routing\RouteLoader;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\ApiVersionMiddleware;
use Framework\Middleware\MiddlewarePipeline;

// Initialize container
$container = new Container();

// Create VersionedRouter
$router = new VersionedRouter();

// Register API versions
$router->registerVersion('v1', isDeprecated: true, deprecationMessage: 'Please migrate to v2', sunsetDate: '2025-12-31');
$router->registerVersion('v2', isDeprecated: false);
$router->registerVersion('v3', isDeprecated: false);

// Set default version
$router->setDefaultVersion('v2');

// Load versioned routes
$routeLoader = new RouteLoader($router, $container);
$routeLoader->load(__DIR__ . '/../config/routes/versioned.php');

echo "=== API Versioning Test ===\n\n";

// Test 1: List registered versions
echo "1. Registered API Versions:\n";
echo "---------------------------\n";
$versions = $router->getVersions();
foreach ($versions as $version => $apiVersion) {
    $status = $apiVersion->isDeprecated() ? '(DEPRECATED)' : '(Active)';
    echo "  {$version} {$status}\n";
    if ($apiVersion->isDeprecated()) {
        echo "    Deprecation: {$apiVersion->getDeprecationHeader()}\n";
    }
}
echo "  Default version: " . $router->getDefaultVersion() . "\n\n";

// Test 2: List all versioned routes
echo "2. Registered Versioned Routes:\n";
echo "-------------------------------\n";
$routes = $router->getRoutes();
echo "Total routes: " . count($routes) . "\n\n";

$routesByVersion = [];
foreach ($routes as $route) {
    // Extract version from pattern
    if (preg_match('#^/(v\d+)/#', $route->pattern, $matches)) {
        $version = $matches[1];
        if (!isset($routesByVersion[$version])) {
            $routesByVersion[$version] = [];
        }
        $routesByVersion[$version][] = $route;
    }
}

foreach ($routesByVersion as $version => $versionRoutes) {
    echo "{$version} routes (" . count($versionRoutes) . "):\n";
    foreach ($versionRoutes as $route) {
        $name = $route->name ? " (name: {$route->name})" : "";
        echo sprintf("  %-6s %-50s => %s%s\n", 
            $route->method, 
            $route->pattern, 
            $route->handler,
            $name
        );
    }
    echo "\n";
}

// Test 3: Route matching with explicit version
echo "3. Route Matching with Explicit Version:\n";
echo "----------------------------------------\n";

$testRequests = [
    ['method' => 'GET', 'uri' => '/v1/api/clients', 'description' => 'v1 clients list (deprecated)'],
    ['method' => 'GET', 'uri' => '/v2/api/clients', 'description' => 'v2 clients list (current)'],
    ['method' => 'GET', 'uri' => '/v3/api/clients', 'description' => 'v3 clients list (beta)'],
    ['method' => 'GET', 'uri' => '/v2/api/clients/123', 'description' => 'v2 client show with ID'],
    ['method' => 'GET', 'uri' => '/v3/api/clients/456/analytics', 'description' => 'v3 new analytics endpoint'],
];

foreach ($testRequests as $test) {
    $request = new Request(
        method: $test['method'],
        uri: $test['uri'],
        headers: [],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $match = $router->match($request);
    
    if ($match) {
        $version = $match->getVersion();
        $versionInfo = $version ? " [Version: {$version->version}]" : "";
        $deprecated = $version && $version->isDeprecated() ? " ⚠️  DEPRECATED" : "";
        echo "✓ {$test['description']}\n";
        echo "  Matched: {$match->route->handler}{$versionInfo}{$deprecated}\n";
        if (!empty($match->params)) {
            echo "  Params: " . json_encode($match->params) . "\n";
        }
    } else {
        echo "✗ {$test['description']}: No match found\n";
    }
    echo "\n";
}

// Test 4: Default version fallback
echo "4. Default Version Fallback:\n";
echo "----------------------------\n";

$fallbackRequests = [
    ['method' => 'GET', 'uri' => '/api/clients', 'description' => 'No version specified'],
    ['method' => 'GET', 'uri' => '/api/projects', 'description' => 'No version specified'],
];

foreach ($fallbackRequests as $test) {
    $request = new Request(
        method: $test['method'],
        uri: $test['uri'],
        headers: [],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $match = $router->match($request);
    
    if ($match) {
        $version = $match->getVersion();
        $versionInfo = $version ? " [Fallback to: {$version->version}]" : "";
        echo "✓ {$test['description']}\n";
        echo "  Matched: {$match->route->handler}{$versionInfo}\n";
    } else {
        echo "✗ {$test['description']}: No match found\n";
    }
    echo "\n";
}

// Test 5: Deprecation headers simulation
echo "5. Deprecation Headers Simulation:\n";
echo "----------------------------------\n";

$deprecationTest = new Request(
    method: 'GET',
    uri: '/v1/api/clients',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($deprecationTest);

if ($match) {
    // Store route match in request attributes
    $deprecationTest = $deprecationTest->withAttribute('route_match', $match);
    
    // Create a mock response
    $mockResponse = new Response(['data' => 'test'], 200);
    
    // Create middleware pipeline with ApiVersionMiddleware
    $middleware = new ApiVersionMiddleware();
    
    // Create a simple handler that returns the mock response
    $handler = new class($mockResponse) implements Framework\Middleware\RequestHandler {
        public function __construct(private Response $response) {}
        
        public function handle(Request $request): Response {
            return $this->response;
        }
    };
    
    // Process through middleware
    $response = $middleware->process($deprecationTest, $handler);
    
    echo "Request: GET /v1/api/clients\n";
    echo "Response Headers:\n";
    foreach ($response->headers as $name => $value) {
        echo "  {$name}: {$value}\n";
    }
} else {
    echo "✗ Could not match route for deprecation test\n";
}

echo "\n";

// Test 6: Version-specific named routes
echo "6. Version-Specific Named Routes:\n";
echo "---------------------------------\n";

$namedRouteTests = [
    ['name' => 'v1.clients.index', 'params' => [], 'expected' => '/v1/api/clients'],
    ['name' => 'v2.clients.show', 'params' => ['id' => 123], 'expected' => '/v2/api/clients/123'],
    ['name' => 'v3.clients.analytics', 'params' => ['id' => 456], 'expected' => '/v3/api/clients/456/analytics'],
];

foreach ($namedRouteTests as $test) {
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

echo "\n=== Test Complete ===\n";
