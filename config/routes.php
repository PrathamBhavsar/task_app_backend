<?php

use Framework\Routing\Router;
use Framework\Middleware\AuthenticationMiddleware;
use Framework\Middleware\RateLimitMiddleware;

/**
 * Application Routes Configuration
 * 
 * This file defines all application routes organized by resource.
 * Routes support:
 * - Route grouping with shared prefixes and middleware
 * - Named routes for URL generation
 * - Route-specific middleware
 * - Parameter constraints
 */

return function (Router $router) {
    // ============================================
    // Public Routes (No Authentication Required)
    // ============================================
    
    // Root health page - shows API status
    $router->get('/', 'Interface\\Controller\\RootHealthController@index')
        ->name('root.health');
    
    // Health check endpoint (JSON)
    $router->get('/health', 'Interface\\Http\\Controllers\\HealthController@check')
        ->name('health.check');
    
    // Swagger UI
    $router->get('/swagger', 'Interface\\Controller\\SwaggerController@ui')
        ->name('swagger.ui');
    
    // Swagger JSON
    $router->get('/swagger/json', 'Interface\\Controller\\SwaggerController@json')
        ->name('swagger.json');
    
    // Metrics endpoint
    $router->get('/metrics', 'Interface\\Controller\\MetricsController@export')
        ->name('metrics.export');

    // ============================================
    // Authentication Routes
    // ============================================
    
    $router->addGroup('/api/auth', function (Router $router) {
        $router->post('/login', 'Interface\\Controller\\AuthController@login')
            ->name('auth.login');
        
        $router->post('/register', 'Interface\\Controller\\AuthController@register')
            ->name('auth.register');
        
        $router->post('/refresh', 'Interface\\Controller\\AuthController@refresh')
            ->name('auth.refresh');
        
        // Logout requires authentication
        $router->post('/logout', 'Interface\\Controller\\AuthController@logout', [AuthenticationMiddleware::class])
            ->name('auth.logout');
    });

    // ============================================
    // Protected API Routes (Authentication Required)
    // ============================================
    
    $router->addGroup('/api', function (Router $router) {
        
        // Client Resource Routes
        $router->addGroup('/clients', function (Router $router) {
            $router->get('', 'Interface\\Controller\\ClientController@index')
                ->name('clients.index');
            
            $router->post('', 'Interface\\Controller\\ClientController@store')
                ->name('clients.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\ClientController@show')
                ->name('clients.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\ClientController@update')
                ->name('clients.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\ClientController@destroy')
                ->name('clients.destroy');
        });

        // Designer Resource Routes
        $router->addGroup('/designers', function (Router $router) {
            $router->get('', 'Interface\\Controller\\DesignerController@index')
                ->name('designers.index');
            
            $router->post('', 'Interface\\Controller\\DesignerController@store')
                ->name('designers.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\DesignerController@show')
                ->name('designers.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\DesignerController@update')
                ->name('designers.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\DesignerController@destroy')
                ->name('designers.destroy');
        });

        // Service Master Resource Routes
        $router->addGroup('/service-master', function (Router $router) {
            $router->get('', 'Interface\\Controller\\ServiceMasterController@index')
                ->name('service-master.index');
            
            $router->post('', 'Interface\\Controller\\ServiceMasterController@store')
                ->name('service-master.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\ServiceMasterController@show')
                ->name('service-master.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\ServiceMasterController@update')
                ->name('service-master.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\ServiceMasterController@destroy')
                ->name('service-master.destroy');
        });

        // Service Resource Routes
        $router->addGroup('/services', function (Router $router) {
            $router->get('', 'Interface\\Controller\\ServiceController@index')
                ->name('services.index');
            
            $router->post('', 'Interface\\Controller\\ServiceController@store')
                ->name('services.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\ServiceController@show')
                ->name('services.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\ServiceController@update')
                ->name('services.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\ServiceController@destroy')
                ->name('services.destroy');
        });

        // Quote Resource Routes
        $router->addGroup('/quotes', function (Router $router) {
            $router->get('', 'Interface\\Controller\\QuoteController@index')
                ->name('quotes.index');
            
            $router->post('', 'Interface\\Controller\\QuoteController@store')
                ->name('quotes.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\QuoteController@show')
                ->name('quotes.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\QuoteController@update')
                ->name('quotes.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\QuoteController@destroy')
                ->name('quotes.destroy');
        });

        // Timeline Resource Routes
        $router->addGroup('/timelines', function (Router $router) {
            $router->get('', 'Interface\\Controller\\TimelineController@index')
                ->name('timelines.index');
            
            $router->get('/task/{task_id:\d+}', 'Interface\\Controller\\TimelineController@getByTaskId')
                ->name('timelines.byTask');
            
            $router->post('', 'Interface\\Controller\\TimelineController@store')
                ->name('timelines.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\TimelineController@show')
                ->name('timelines.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\TimelineController@update')
                ->name('timelines.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\TimelineController@destroy')
                ->name('timelines.destroy');
        });

        // Task Message Resource Routes
        $router->addGroup('/messages', function (Router $router) {
            $router->get('', 'Interface\\Controller\\TaskMessageController@index')
                ->name('messages.index');
            
            $router->get('/task/{task_id:\d+}', 'Interface\\Controller\\TaskMessageController@getByTaskId')
                ->name('messages.byTask');
            
            $router->post('/task/{task_id:\d+}', 'Interface\\Controller\\TaskMessageController@storeByTask')
                ->name('messages.storeByTask');
            
            $router->post('', 'Interface\\Controller\\TaskMessageController@store')
                ->name('messages.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\TaskMessageController@show')
                ->name('messages.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\TaskMessageController@update')
                ->name('messages.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\TaskMessageController@destroy')
                ->name('messages.destroy');
        });

        // Measurement Resource Routes
        $router->addGroup('/measurements', function (Router $router) {
            $router->get('', 'Interface\\Controller\\MeasurementController@index')
                ->name('measurements.index');
            
            $router->post('', 'Interface\\Controller\\MeasurementController@store')
                ->name('measurements.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\MeasurementController@show')
                ->name('measurements.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\MeasurementController@update')
                ->name('measurements.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\MeasurementController@destroy')
                ->name('measurements.destroy');
        });

        // Project Resource Routes
        $router->addGroup('/projects', function (Router $router) {
            $router->get('', 'Interface\\Controller\\ProjectController@index')
                ->name('projects.index');
            
            $router->post('', 'Interface\\Controller\\ProjectController@store')
                ->name('projects.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\ProjectController@show')
                ->name('projects.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\ProjectController@update')
                ->name('projects.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\ProjectController@destroy')
                ->name('projects.destroy');
        });

        // Task Resource Routes
        $router->addGroup('/tasks', function (Router $router) {
            $router->get('', 'Interface\\Controller\\TaskController@index')
                ->name('tasks.index');
            
            $router->post('', 'Interface\\Controller\\TaskController@store')
                ->name('tasks.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\TaskController@show')
                ->name('tasks.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\TaskController@update')
                ->name('tasks.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\TaskController@destroy')
                ->name('tasks.destroy');
            
            $router->patch('/{id:\d+}/status', 'Interface\\Controller\\TaskController@updateStatus')
                ->name('tasks.updateStatus');
        });

        // Bill Resource Routes
        $router->addGroup('/bills', function (Router $router) {
            $router->get('', 'Interface\\Controller\\BillController@index')
                ->name('bills.index');
            
            $router->post('', 'Interface\\Controller\\BillController@store')
                ->name('bills.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\BillController@show')
                ->name('bills.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\BillController@update')
                ->name('bills.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\BillController@destroy')
                ->name('bills.destroy');
        });

        // User Resource Routes
        $router->addGroup('/users', function (Router $router) {
            $router->get('', 'Interface\\Controller\\UserController@index')
                ->name('users.index');
            
            $router->post('', 'Interface\\Controller\\UserController@store')
                ->name('users.store');
            
            $router->get('/{id:\d+}', 'Interface\\Controller\\UserController@show')
                ->name('users.show');
            
            $router->put('/{id:\d+}', 'Interface\\Controller\\UserController@update')
                ->name('users.update');
            
            $router->delete('/{id:\d+}', 'Interface\\Controller\\UserController@destroy')
                ->name('users.destroy');
        });
        
    }, [AuthenticationMiddleware::class]); // Apply authentication to all /api routes
};
