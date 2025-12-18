<?php

/**
 * Example route configuration for metrics endpoints
 * 
 * Add these routes to your config/routes.php file
 */

use Interface\Http\Controllers\MetricsController;

// Metrics endpoints (typically protected or internal only)
$router->addRoute('GET', '/metrics', MetricsController::class . '@prometheus');
$router->addRoute('GET', '/metrics/json', MetricsController::class . '@json');
$router->addRoute('GET', '/metrics/custom', MetricsController::class . '@custom');

// Note: In production, these endpoints should be:
// 1. Protected by authentication/authorization
// 2. Only accessible from internal networks
// 3. Or exposed only to monitoring systems (e.g., Prometheus)
//
// Example with authentication:
// $router->addRoute('GET', '/metrics', MetricsController::class . '@prometheus', [
//     AuthenticationMiddleware::class
// ]);
