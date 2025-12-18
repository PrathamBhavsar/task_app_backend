<?php

use Framework\Routing\Router;
use Framework\Middleware\AuthenticationMiddleware;

/**
 * API Routes
 * 
 * Protected API routes that require authentication
 */

return function (Router $router) {
    $router->addGroup('/api', function (Router $router) {
        
        // Client Resource Routes
        $router->addGroup('/clients', function (Router $router) {
            $router->get('', 'ClientController@index')
                ->name('clients.index');
            
            $router->post('', 'ClientController@store')
                ->name('clients.store');
            
            $router->get('/{id:\d+}', 'ClientController@show')
                ->name('clients.show');
            
            $router->put('/{id:\d+}', 'ClientController@update')
                ->name('clients.update');
            
            $router->delete('/{id:\d+}', 'ClientController@destroy')
                ->name('clients.destroy');
        });

        // Project Resource Routes
        $router->addGroup('/projects', function (Router $router) {
            $router->get('', 'ProjectController@index')
                ->name('projects.index');
            
            $router->post('', 'ProjectController@store')
                ->name('projects.store');
            
            $router->get('/{id:\d+}', 'ProjectController@show')
                ->name('projects.show');
            
            $router->put('/{id:\d+}', 'ProjectController@update')
                ->name('projects.update');
            
            $router->delete('/{id:\d+}', 'ProjectController@destroy')
                ->name('projects.destroy');
        });

        // Task Resource Routes
        $router->addGroup('/tasks', function (Router $router) {
            $router->get('', 'TaskController@index')
                ->name('tasks.index');
            
            $router->post('', 'TaskController@store')
                ->name('tasks.store');
            
            $router->get('/{id:\d+}', 'TaskController@show')
                ->name('tasks.show');
            
            $router->put('/{id:\d+}', 'TaskController@update')
                ->name('tasks.update');
            
            $router->delete('/{id:\d+}', 'TaskController@destroy')
                ->name('tasks.destroy');
            
            $router->patch('/{id:\d+}/status', 'TaskController@updateStatus')
                ->name('tasks.updateStatus');
        });

        // Bill Resource Routes
        $router->addGroup('/bills', function (Router $router) {
            $router->get('', 'BillController@index')
                ->name('bills.index');
            
            $router->post('', 'BillController@store')
                ->name('bills.store');
            
            $router->get('/{id:\d+}', 'BillController@show')
                ->name('bills.show');
            
            $router->put('/{id:\d+}', 'BillController@update')
                ->name('bills.update');
            
            $router->delete('/{id:\d+}', 'BillController@destroy')
                ->name('bills.destroy');
        });

        // User Resource Routes
        $router->addGroup('/users', function (Router $router) {
            $router->get('', 'UserController@index')
                ->name('users.index');
            
            $router->get('/{id:\d+}', 'UserController@show')
                ->name('users.show');
            
            $router->put('/{id:\d+}', 'UserController@update')
                ->name('users.update');
        });
        
    }, [AuthenticationMiddleware::class]); // Apply authentication to all /api routes
};
