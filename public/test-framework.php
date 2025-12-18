<?php

declare(strict_types=1);

// Load environment and autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\EnvLoader;
use Framework\Config\Config;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Container\Container;
use Framework\Routing\Router;
use Framework\Middleware\MiddlewarePipeline;
use Framework\Middleware\RequestHandler;

// Load environment variables
$envLoader = new EnvLoader();
$envLoader->load(__DIR__ . '/../.env');

// Create config
$config = new Config([
    'app' => require __DIR__ . '/../config/app.php',
    'database' => [
        'host' => $_ENV['DB_HOST'],
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS'],
    ],
]);

// Create container
$container = new Container();
$container->instance(Config::class, $config);

// Create router
$router = new Router();

// Define test routes
$router->get('/test', 'TestController@index');
$router->get('/test/hello', 'TestController@hello');
$router->get('/test/config', 'TestController@config');
$router->get('/test/user/{id}', 'TestController@user');

// Create request from globals
$request = Request::fromGlobals();

// For PHP built-in server, use query parameter for route
$path = $_GET['path'] ?? '/test';
$testRequest = new Request(
    method: $request->method,
    uri: $path,
    headers: $request->headers,
    query: $request->query,
    body: $request->body,
    files: $request->files,
    server: $request->server,
    attributes: $request->attributes
);

// Try to match route
$match = $router->match($testRequest);

if ($match === null) {
    $response = new Response([
        'success' => false,
        'error' => 'Route not found',
        'available_routes' => [
            'GET /test',
            'GET /test/hello',
            'GET /test/config',
            'GET /test/user/{id}',
        ]
    ], 404);
    $response->send();
    exit;
}

// Simple handler for test routes
$handler = new class($match, $config) implements RequestHandler {
    public function __construct(
        private $match,
        private Config $config
    ) {}
    
    public function handle(Request $request): Response
    {
        $handler = $this->match->route->handler;
        [$controller, $method] = explode('@', $handler);
        
        // Simple test controller logic
        if ($controller === 'TestController') {
            return match($method) {
                'index' => new Response([
                    'success' => true,
                    'message' => 'Framework is working!',
                    'framework' => 'Custom PHP Framework',
                    'version' => '1.0.0'
                ]),
                'hello' => new Response([
                    'success' => true,
                    'message' => 'Hello from the new framework!',
                    'timestamp' => date('Y-m-d H:i:s')
                ]),
                'config' => new Response([
                    'success' => true,
                    'config' => [
                        'app_env' => $this->config->get('app.env'),
                        'app_debug' => $this->config->get('app.debug'),
                        'db_host' => $this->config->get('database.host'),
                        'db_name' => $this->config->get('database.name'),
                    ]
                ]),
                'user' => new Response([
                    'success' => true,
                    'message' => 'User route with parameter',
                    'user_id' => $this->match->params['id'] ?? null,
                    'query_params' => $this->match->queryParams
                ]),
                default => new Response(['error' => 'Method not found'], 404)
            };
        }
        
        return new Response(['error' => 'Controller not found'], 404);
    }
};

// Create middleware pipeline
$pipeline = new MiddlewarePipeline();
$pipeline->then($handler);

// Process request
$response = $pipeline->handle($request);

// Send response
$response->send();
