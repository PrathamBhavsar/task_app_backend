<?php

use Framework\Routing\Router;

/**
 * System Routes
 * 
 * Public system endpoints for health checks and metrics
 */

return function (Router $router) {
    // Health check endpoint
    $router->get('/health', 'HealthController@check')
        ->name('health.check');
    
    // Metrics endpoint
    $router->get('/metrics', 'MetricsController@export')
        ->name('metrics.export');
};
