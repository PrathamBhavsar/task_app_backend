<?php

declare(strict_types=1);

/**
 * Application Entry Point
 * 
 * This file serves as the main entry point for the application.
 * It bootstraps the Framework layer and handles incoming HTTP requests.
 */

// Bootstrap Application
$app = require __DIR__ . '/../bootstrap.php';

/** @var Framework\Container\Container $container */
$container = $app['container'];

/** @var Framework\Routing\Router $router */
$router = $app['router'];

/** @var Framework\Middleware\MiddlewarePipeline $pipeline */
$pipeline = $app['pipeline'];

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\RequestHandler;
use Framework\Error\NotFoundException;

// Create Request from Globals
$request = Request::fromGlobals();

// Define Request Handler
$requestHandler = new class($router, $container, $request) implements RequestHandler {
    public function __construct(
        private \Framework\Routing\Router $router,
        private \Framework\Container\Container $container,
        private Request $request
    ) {}

    public function handle(Request $request): Response
    {
        // Try to match route using new router
        $routeMatch = $this->router->match($request);
        
        if ($routeMatch !== null) {
            return $this->handleNewRoute($routeMatch, $request);
        }
        
        // Fall back to legacy route handling for backward compatibility
        return $this->handleLegacyRoute($request);
    }
    
    private function handleNewRoute(\Framework\Routing\RouteMatch $routeMatch, Request $request): Response
    {
        $route = $routeMatch->route;
        
        // Store route match in request attributes
        $request = $request->withAttribute('route', $route->pattern);
        $request = $request->withAttribute('route_params', $routeMatch->params);
        
        // Apply route-specific middleware
        $routePipeline = new \Framework\Middleware\MiddlewarePipeline();
        
        foreach ($route->middleware as $middlewareClass) {
            $middleware = $this->container->resolve($middlewareClass);
            $routePipeline->pipe($middleware);
        }
        
        // Create final handler that calls the controller
        $finalHandler = new class($route, $routeMatch, $this->container, $request) implements RequestHandler {
            public function __construct(
                private \Framework\Routing\Route $route,
                private \Framework\Routing\RouteMatch $routeMatch,
                private \Framework\Container\Container $container,
                private Request $request
            ) {}
            
            public function handle(Request $request): Response
            {
                // Parse handler (ControllerClass@method)
                [$controllerClass, $method] = explode('@', $this->route->handler);
                
                // Resolve controller from container
                $controller = $this->container->resolve($controllerClass);
                
                // Store route params in request attributes
                foreach ($this->routeMatch->params as $key => $value) {
                    $request = $request->withAttribute($key, $value);
                }
                
                // Call controller method with only the request parameter
                $result = $controller->$method($request);
                
                // If result is already a Response, return it
                if ($result instanceof Response) {
                    return $result;
                }
                
                // Otherwise wrap in Response
                return new Response($result, 200);
            }
        };
        
        // Process through route middleware
        return $routePipeline->then($finalHandler)->handle($request);
    }
    
    private function handleLegacyRoute(Request $request): Response
    {
        // Load legacy controllers
        require_once __DIR__ . '/../helpers/response.php';
        require_once __DIR__ . '/../errorHandler.php';
        require_once __DIR__ . '/../config/controllers.php';
        
        // Parse legacy route format
        $requestMethod = $request->method;
        $requestUri = $request->getPath();
        $segments = explode('/', trim($requestUri, '/'));
        $resource = $segments[1] ?? null;
        $id = $request->query['id'] ?? null;
        $body = $request->body;
        
        // Check if route exists
        if ($segments[0] !== 'api') {
            throw new NotFoundException("Route not found: {$requestUri}");
        }
        
        // Store controllers in global scope for legacy route handlers
        if (isset($designerController)) $GLOBALS['designerController'] = $designerController;
        if (isset($clientController)) $GLOBALS['clientController'] = $clientController;
        if (isset($userController)) $GLOBALS['userController'] = $userController;
        if (isset($taskController)) $GLOBALS['taskController'] = $taskController;
        if (isset($timelineController)) $GLOBALS['timelineController'] = $timelineController;
        if (isset($taskMessageController)) $GLOBALS['taskMessageController'] = $taskMessageController;
        if (isset($measurementController)) $GLOBALS['measurementController'] = $measurementController;
        if (isset($serviceController)) $GLOBALS['serviceController'] = $serviceController;
        if (isset($quoteController)) $GLOBALS['quoteController'] = $quoteController;
        if (isset($serviceMasterController)) $GLOBALS['serviceMasterController'] = $serviceMasterController;
        if (isset($billController)) $GLOBALS['billController'] = $billController;
        if (isset($authController)) $GLOBALS['authController'] = $authController;
        
        // Legacy routes map
        $routes = $this->getLegacyRoutes($segments, $request);
        
        if (!isset($routes[$resource])) {
            throw new NotFoundException("Route not found: {$requestUri}");
        }
        
        // Capture output from legacy route handler
        ob_start();
        $routes[$resource]($requestMethod, $id, $body);
        $output = ob_get_clean();
        
        // If output was sent, wrap it in a Response
        if (!empty($output)) {
            // Try to decode as JSON to get proper structure
            $decoded = json_decode($output, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return new Response($decoded, http_response_code() ?: 200);
            }
            return new Response(['data' => $output], http_response_code() ?: 200);
        }
        
        // No output, return empty success response
        return new Response(['success' => true], 200);
    }
    
    private function getLegacyRoutes(array $segments, Request $request): array
    {
        return [
            'designer' => fn($method, $id, $body) => match ($method) {
                'GET'    => $id ? $GLOBALS['designerController']->show((int)$id) : $GLOBALS['designerController']->index(),
                'POST'   => $GLOBALS['designerController']->store($body),
                'PUT'    => $id ? $GLOBALS['designerController']->update((int)$id, $body) : sendError("ID required", 400),
                'DELETE' => $id ? $GLOBALS['designerController']->delete((int)$id) : sendError("ID required", 400),
                default  => sendError("Method not allowed", 405)
            },
            
            'client' => fn($method, $id, $body) => match ($method) {
                'GET'    => $id ? $GLOBALS['clientController']->show((int)$id) : $GLOBALS['clientController']->index(),
                'POST'   => $GLOBALS['clientController']->store($body),
                'PUT'    => $id ? $GLOBALS['clientController']->update((int)$id, $body) : sendError("ID required", 400),
                'DELETE' => $id ? $GLOBALS['clientController']->delete((int)$id) : sendError("ID required", 400),
                default  => sendError("Method not allowed", 405)
            },
            
            'user' => fn($method, $id, $body) => match ($method) {
                'GET'    => $id ? $GLOBALS['userController']->show((int)$id) : $GLOBALS['userController']->index(),
                'POST'   => $GLOBALS['userController']->store($body),
                'PUT'    => $id ? $GLOBALS['userController']->update((int)$id, $body) : sendError("ID required", 400),
                'DELETE' => $id ? $GLOBALS['userController']->delete((int)$id) : sendError("ID required", 400),
                default  => sendError("Method not allowed", 405)
            },
            
            'task' => fn($method, $id, $body) => match ($method) {
                'GET' => $id ? $GLOBALS['taskController']->show((int)$id) : $GLOBALS['taskController']->index(),
                'POST' => $GLOBALS['taskController']->store($body),
                'PUT' => match (true) {
                    isset($request->query['id'], $request->query['status']) => $GLOBALS['taskController']->updateStatus((int)$request->query['id'], $request->query['status']),
                    $id => $GLOBALS['taskController']->update((int)$id, $body),
                    default => sendError("ID required", 400)
                },
                'DELETE' => $id ? $GLOBALS['taskController']->delete((int)$id) : sendError("ID required", 400),
                default => sendError("Method not allowed", 405)
            },
            
            'timeline' => fn($method, $id, $body) => match ($method) {
                'GET' => match (true) {
                    isset($request->query['id']) => $GLOBALS['timelineController']->show((int) $request->query['id']),
                    isset($request->query['task_id']) => $GLOBALS['timelineController']->getByTaskId((int) $request->query['task_id']),
                    default => $GLOBALS['timelineController']->index()
                },
                'POST' => $GLOBALS['timelineController']->store($body),
                'PUT' => isset($request->query['id']) ? $GLOBALS['timelineController']->update((int) $request->query['id'], $body) : sendError("ID required", 400),
                'DELETE' => isset($request->query['id']) ? $GLOBALS['timelineController']->delete((int) $request->query['id']) : sendError("ID required", 400),
                default => sendError("Method not allowed", 405)
            },
            
            'message' => fn($method, $id, $body) => match ($method) {
                'GET' => match (true) {
                    isset($request->query['id']) => $GLOBALS['taskMessageController']->show((int) $request->query['id']),
                    isset($request->query['task_id']) => $GLOBALS['taskMessageController']->getByTaskId((int) $request->query['task_id']),
                    default => $GLOBALS['taskMessageController']->index()
                },
                'POST' => $GLOBALS['taskMessageController']->store($body),
                'PUT' => isset($request->query['id']) ? $GLOBALS['taskMessageController']->update((int) $request->query['id'], $body) : sendError("ID required", 400),
                'DELETE' => isset($request->query['id']) ? $GLOBALS['taskMessageController']->delete((int) $request->query['id']) : sendError("ID required", 400),
                default => sendError("Method not allowed", 405)
            },
            
            'measurement' => fn($method, $id, $body) => match ($method) {
                'GET' => match (true) {
                    isset($request->query['id']) => $GLOBALS['measurementController']->show((int) $request->query['id']),
                    isset($request->query['task_id']) => $GLOBALS['measurementController']->getByTaskId((int) $request->query['task_id']),
                    default => $GLOBALS['measurementController']->index()
                },
                'POST' => $GLOBALS['measurementController']->store($body),
                'PUT' => isset($request->query['id']) ? $GLOBALS['measurementController']->update((int) $request->query['id'], $body) : sendError("ID required", 400),
                'DELETE' => isset($request->query['id']) ? $GLOBALS['measurementController']->delete((int) $request->query['id']) : sendError("ID required", 400),
                default => sendError("Method not allowed", 405)
            },
            
            'service' => fn($method, $id, $body) => match ($method) {
                'GET' => match (true) {
                    isset($request->query['id']) => $GLOBALS['serviceController']->show((int) $request->query['id']),
                    isset($request->query['task_id']) => $GLOBALS['serviceController']->getByTaskId((int) $request->query['task_id']),
                    default => $GLOBALS['serviceController']->index()
                },
                'POST' => $GLOBALS['serviceController']->store($body),
                'PUT' => isset($request->query['id']) ? $GLOBALS['serviceController']->update((int) $request->query['id'], $body) : sendError("ID required", 400),
                'DELETE' => isset($request->query['id']) ? $GLOBALS['serviceController']->delete((int) $request->query['id']) : sendError("ID required", 400),
                default => sendError("Method not allowed", 405)
            },
            
            'quote' => fn($method, $id, $body) => match ($method) {
                'GET' => match (true) {
                    isset($request->query['id']) => $GLOBALS['quoteController']->show((int) $request->query['id']),
                    isset($request->query['task_id']) => $GLOBALS['quoteController']->getByTaskId((int) $request->query['task_id']),
                    default => $GLOBALS['quoteController']->index()
                },
                'POST'   => $GLOBALS['quoteController']->store($body),
                'PUT'    => $id ? $GLOBALS['quoteController']->update((int)$id, $body) : sendError("ID required", 400),
                'DELETE' => $id ? $GLOBALS['quoteController']->delete((int)$id) : sendError("ID required", 400),
                default  => sendError("Method not allowed", 405)
            },
            
            'service-master' => fn($method, $id, $body) => match ($method) {
                'GET'    => $id ? $GLOBALS['serviceMasterController']->show((int)$id) : $GLOBALS['serviceMasterController']->index(),
                'POST'   => $GLOBALS['serviceMasterController']->store($body),
                'PUT'    => $id ? $GLOBALS['serviceMasterController']->update((int)$id, $body) : sendError("ID required", 400),
                'DELETE' => $id ? $GLOBALS['serviceMasterController']->delete((int)$id) : sendError("ID required", 400),
                default  => sendError("Method not allowed", 405)
            },
            
            'bill' => fn($method, $id, $body) => match ($method) {
                'GET'    => $id ? $GLOBALS['billController']->show((int)$id) : $GLOBALS['billController']->index(),
                'POST'   => $GLOBALS['billController']->store($body),
                'PUT'    => $id ? $GLOBALS['billController']->update((int)$id, $body) : sendError("ID required", 400),
                'DELETE' => $id ? $GLOBALS['billController']->delete((int)$id) : sendError("ID required", 400),
                default  => sendError("Method not allowed", 405)
            },
            
            'auth' => fn($method, $id, $body) => match ($method) {
                'POST' => match ($segments[2] ?? null) {
                    'login' => $GLOBALS['authController']->login($body),
                    'register' => $GLOBALS['authController']->register($body),
                    'refresh' => $GLOBALS['authController']->refreshToken($body),
                    default => sendError("Missing or invalid action", 400)
                },
                default => sendError("Method not allowed", 405)
            }
        ];
    }
};

// Process Request Through Pipeline
$response = $pipeline->then($requestHandler)->handle($request);

// Send Response
$response->send();
