<?php

/**
 * Integration Test for Client Endpoints
 * 
 * This script tests the full request flow through the routing system
 * for Client endpoints using the new architecture.
 */

require_once __DIR__ . '/../bootstrap.php';

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Routing\Router;
use Framework\Container\Container;
use Framework\Middleware\MiddlewarePipeline;
use Framework\Middleware\ErrorHandlerMiddleware;
use Framework\Middleware\CorsMiddleware;

echo "=== Client Endpoints Integration Test ===\n\n";

// Initialize container
$container = new Container();

// Initialize router
$router = new Router($container);

// Load only the API routes
$routeLoader = require __DIR__ . '/../config/routes/api.php';
if (is_callable($routeLoader)) {
    $routeLoader($router);
}

echo "Routes loaded successfully\n\n";

// Test 1: Match GET /api/clients route
echo "Test 1: Match GET /api/clients\n";
echo "--------------------------------\n";

$request = new Request(
    method: 'GET',
    uri: '/api/clients',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($request);

if ($match) {
    echo "✓ Route matched successfully\n";
    echo "  Handler: " . $match->route->handler . "\n";
    echo "  Method: " . $match->route->method . "\n";
    echo "  Pattern: " . $match->route->pattern . "\n";
    echo "  Middleware: " . implode(', ', $match->route->middleware) . "\n";
} else {
    echo "✗ Route not matched\n";
}

echo "\n";

// Test 2: Match POST /api/clients route
echo "Test 2: Match POST /api/clients\n";
echo "---------------------------------\n";

$request = new Request(
    method: 'POST',
    uri: '/api/clients',
    headers: ['Content-Type' => 'application/json'],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($request);

if ($match) {
    echo "✓ Route matched successfully\n";
    echo "  Handler: " . $match->route->handler . "\n";
    echo "  Method: " . $match->route->method . "\n";
    echo "  Pattern: " . $match->route->pattern . "\n";
    echo "  Middleware: " . implode(', ', $match->route->middleware) . "\n";
} else {
    echo "✗ Route not matched\n";
}

echo "\n";

// Test 3: Match GET /api/clients/{id} route
echo "Test 3: Match GET /api/clients/123\n";
echo "------------------------------------\n";

$request = new Request(
    method: 'GET',
    uri: '/api/clients/123',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($request);

if ($match) {
    echo "✓ Route matched successfully\n";
    echo "  Handler: " . $match->route->handler . "\n";
    echo "  Method: " . $match->route->method . "\n";
    echo "  Pattern: " . $match->route->pattern . "\n";
    echo "  Parameters: " . json_encode($match->params) . "\n";
    echo "  Middleware: " . implode(', ', $match->route->middleware) . "\n";
} else {
    echo "✗ Route not matched\n";
}

echo "\n";

// Test 4: Match PUT /api/clients/{id} route
echo "Test 4: Match PUT /api/clients/456\n";
echo "------------------------------------\n";

$request = new Request(
    method: 'PUT',
    uri: '/api/clients/456',
    headers: ['Content-Type' => 'application/json'],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($request);

if ($match) {
    echo "✓ Route matched successfully\n";
    echo "  Handler: " . $match->route->handler . "\n";
    echo "  Method: " . $match->route->method . "\n";
    echo "  Pattern: " . $match->route->pattern . "\n";
    echo "  Parameters: " . json_encode($match->params) . "\n";
    echo "  Middleware: " . implode(', ', $match->route->middleware) . "\n";
} else {
    echo "✗ Route not matched\n";
}

echo "\n";

// Test 5: Match DELETE /api/clients/{id} route
echo "Test 5: Match DELETE /api/clients/789\n";
echo "---------------------------------------\n";

$request = new Request(
    method: 'DELETE',
    uri: '/api/clients/789',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($request);

if ($match) {
    echo "✓ Route matched successfully\n";
    echo "  Handler: " . $match->route->handler . "\n";
    echo "  Method: " . $match->route->method . "\n";
    echo "  Pattern: " . $match->route->pattern . "\n";
    echo "  Parameters: " . json_encode($match->params) . "\n";
    echo "  Middleware: " . implode(', ', $match->route->middleware) . "\n";
} else {
    echo "✗ Route not matched\n";
}

echo "\n";

// Test 6: Test invalid route
echo "Test 6: Test invalid route\n";
echo "---------------------------\n";

$request = new Request(
    method: 'GET',
    uri: '/api/clients/invalid',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

$match = $router->match($request);

if ($match) {
    echo "✗ Route matched (should not match)\n";
} else {
    echo "✓ Route not matched (as expected)\n";
}

echo "\n";

// Test 7: Verify DTO structure
echo "Test 7: Verify DTO structure\n";
echo "-----------------------------\n";

use Interface\Http\DTO\Request\CreateClientRequest;
use Interface\Http\DTO\Request\UpdateClientRequest;
use Interface\Http\DTO\Response\ClientResponse;

$createDto = CreateClientRequest::fromArray([
    'name' => 'Test Client',
    'contact_no' => '+1234567890',
    'address' => '123 Test St',
    'email' => 'test@example.com'
]);

echo "✓ CreateClientRequest DTO created\n";
echo "  Name: " . $createDto->name . "\n";
echo "  Email: " . $createDto->email . "\n";

$updateDto = UpdateClientRequest::fromArray([
    'name' => 'Updated Client',
    'address' => '456 New St'
]);

echo "✓ UpdateClientRequest DTO created\n";
echo "  Provided fields: " . json_encode($updateDto->getProvidedFields()) . "\n";

echo "\n";

// Test 8: Verify ApiResponse structure
echo "Test 8: Verify ApiResponse structure\n";
echo "--------------------------------------\n";

use Interface\Http\DTO\ApiResponse;

$successResponse = ApiResponse::success(['id' => 1, 'name' => 'Test']);
echo "✓ Success response created\n";
echo "  Status: " . $successResponse->status . "\n";
echo "  Body: " . $successResponse->toJson() . "\n\n";

$errorResponse = ApiResponse::error('Test error', 400);
echo "✓ Error response created\n";
echo "  Status: " . $errorResponse->status . "\n";
echo "  Body: " . $errorResponse->toJson() . "\n\n";

$validationResponse = ApiResponse::validationError(['email' => ['Invalid email']]);
echo "✓ Validation error response created\n";
echo "  Status: " . $validationResponse->status . "\n";
echo "  Body: " . $validationResponse->toJson() . "\n\n";

$notFoundResponse = ApiResponse::notFound('Client not found');
echo "✓ Not found response created\n";
echo "  Status: " . $notFoundResponse->status . "\n";
echo "  Body: " . $notFoundResponse->toJson() . "\n\n";

echo "=== All Integration Tests Completed ===\n";
