<?php

use Framework\Routing\VersionedRouter;
use Framework\Middleware\AuthenticationMiddleware;

/**
 * Versioned API Routes Configuration
 * 
 * This file demonstrates API versioning support.
 * Routes can be registered for specific versions with deprecation warnings.
 */

return function (VersionedRouter $router) {
    // Register API versions
    $router->registerVersion('v1', isDeprecated: true, deprecationMessage: 'Please migrate to v2', sunsetDate: '2025-12-31');
    $router->registerVersion('v2', isDeprecated: false);
    $router->registerVersion('v3', isDeprecated: false);

    // Set default version (used when no version is specified in the URL)
    $router->setDefaultVersion('v2');

    // ============================================
    // Version 1 Routes (Deprecated)
    // ============================================
    
    $router->version('v1', function (VersionedRouter $router) {
        $router->addGroup('/api', function (VersionedRouter $router) {
            // Old client endpoints with different structure
            $router->get('/clients', 'ClientControllerV1@index')
                ->name('v1.clients.index');
            
            $router->get('/clients/{id:\d+}', 'ClientControllerV1@show')
                ->name('v1.clients.show');
        }, [AuthenticationMiddleware::class]);
    });

    // ============================================
    // Version 2 Routes (Current Stable)
    // ============================================
    
    $router->version('v2', function (VersionedRouter $router) {
        $router->addGroup('/api', function (VersionedRouter $router) {
            // Client Resource Routes
            $router->addGroup('/clients', function (VersionedRouter $router) {
                $router->get('', 'ClientController@index')
                    ->name('v2.clients.index');
                
                $router->post('', 'ClientController@store')
                    ->name('v2.clients.store');
                
                $router->get('/{id:\d+}', 'ClientController@show')
                    ->name('v2.clients.show');
                
                $router->put('/{id:\d+}', 'ClientController@update')
                    ->name('v2.clients.update');
                
                $router->delete('/{id:\d+}', 'ClientController@destroy')
                    ->name('v2.clients.destroy');
            });

            // Project Resource Routes
            $router->addGroup('/projects', function (VersionedRouter $router) {
                $router->get('', 'ProjectController@index')
                    ->name('v2.projects.index');
                
                $router->post('', 'ProjectController@store')
                    ->name('v2.projects.store');
                
                $router->get('/{id:\d+}', 'ProjectController@show')
                    ->name('v2.projects.show');
                
                $router->put('/{id:\d+}', 'ProjectController@update')
                    ->name('v2.projects.update');
                
                $router->delete('/{id:\d+}', 'ProjectController@destroy')
                    ->name('v2.projects.destroy');
            });
        }, [AuthenticationMiddleware::class]);
    });

    // ============================================
    // Version 3 Routes (Beta/Preview)
    // ============================================
    
    $router->version('v3', function (VersionedRouter $router) {
        $router->addGroup('/api', function (VersionedRouter $router) {
            // New enhanced client endpoints with additional features
            $router->addGroup('/clients', function (VersionedRouter $router) {
                $router->get('', 'ClientControllerV3@index')
                    ->name('v3.clients.index');
                
                $router->post('', 'ClientControllerV3@store')
                    ->name('v3.clients.store');
                
                $router->get('/{id:\d+}', 'ClientControllerV3@show')
                    ->name('v3.clients.show');
                
                $router->put('/{id:\d+}', 'ClientControllerV3@update')
                    ->name('v3.clients.update');
                
                $router->delete('/{id:\d+}', 'ClientControllerV3@destroy')
                    ->name('v3.clients.destroy');
                
                // New endpoint in v3
                $router->get('/{id:\d+}/analytics', 'ClientControllerV3@analytics')
                    ->name('v3.clients.analytics');
            });
        }, [AuthenticationMiddleware::class]);
    });
};
