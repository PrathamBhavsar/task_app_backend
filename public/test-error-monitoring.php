<?php

declare(strict_types=1);

/**
 * Test file for error monitoring integration
 * 
 * This demonstrates how to use the error monitoring system with the ErrorHandler
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\EnvLoader;
use Framework\Config\Config;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Error\ErrorHandler;
use Framework\Error\ErrorMonitorFactory;
use Framework\Error\ErrorHandlerMiddleware;
use Framework\Error\NotFoundException;
use Framework\Error\ValidationException;
use Framework\Middleware\MiddlewarePipeline;
use Framework\Middleware\RequestHandler;

// Load environment variables
$envLoader = new EnvLoader();
$envLoader->load(__DIR__ . '/../.env');

// Load monitoring configuration
$monitoringConfig = require __DIR__ . '/../config/monitoring.php';
$appConfig = require __DIR__ . '/../config/app.php';

// Create error monitor
$errorMonitor = ErrorMonitorFactory::create($monitoringConfig);

// Create error handler with monitoring
$errorHandler = new ErrorHandler(
    debug: $appConfig['debug'],
    logPath: __DIR__ . '/../error_log',
    errorMonitor: $errorMonitor,
    reportLevels: $monitoringConfig['report_levels'] ?? [],
    environment: $appConfig['env']
);

// Create request from globals
$request = Request::fromGlobals();

// Get test scenario from query parameter
$scenario = $_GET['scenario'] ?? 'info';

// Create a test handler that throws different types of errors
$testHandler = new class($scenario) implements RequestHandler {
    public function __construct(private string $scenario) {}
    
    public function handle(Request $request): Response
    {
        return match($this->scenario) {
            'not-found' => throw new NotFoundException('Resource not found'),
            
            'validation' => throw new ValidationException([
                'email' => ['The email field is required'],
                'name' => ['The name field must be at least 3 characters']
            ]),
            
            'server-error' => throw new \RuntimeException('Something went wrong on the server'),
            
            'division-by-zero' => throw new \DivisionByZeroError('Cannot divide by zero'),
            
            'info' => new Response([
                'success' => true,
                'message' => 'Error monitoring is configured',
                'monitoring' => [
                    'enabled' => $GLOBALS['monitoringConfig']['enabled'] ?? false,
                    'service' => $GLOBALS['monitoringConfig']['service'] ?? 'none',
                    'environment' => $GLOBALS['appConfig']['env'] ?? 'unknown',
                ],
                'available_scenarios' => [
                    'info' => 'Show monitoring configuration',
                    'not-found' => 'Test NotFoundException (404)',
                    'validation' => 'Test ValidationException (422)',
                    'server-error' => 'Test RuntimeException (500)',
                    'division-by-zero' => 'Test DivisionByZeroError (500)',
                ],
                'usage' => 'Add ?scenario=<scenario> to test different error types'
            ]),
            
            default => new Response([
                'success' => false,
                'error' => 'Unknown scenario',
                'available_scenarios' => ['info', 'not-found', 'validation', 'server-error', 'division-by-zero']
            ], 400)
        };
    }
};

// Store config in globals for access in handler
$GLOBALS['monitoringConfig'] = $monitoringConfig;
$GLOBALS['appConfig'] = $appConfig;

// Create middleware pipeline with error handler
$pipeline = new MiddlewarePipeline();
$pipeline->pipe(new ErrorHandlerMiddleware($errorHandler));
$pipeline->then($testHandler);

// Add user context to request for testing (simulating authenticated user)
if (isset($_GET['user_id'])) {
    $request = $request->withAttribute('user', [
        'id' => $_GET['user_id'],
        'email' => 'test@example.com',
        'username' => 'testuser'
    ]);
}

// Add route context for testing
$request = $request->withAttribute('route', '/test/error-monitoring');

// Process request through pipeline
$response = $pipeline->handle($request);

// Send response
$response->send();
