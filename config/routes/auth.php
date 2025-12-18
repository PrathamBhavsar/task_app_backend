<?php

use Framework\Routing\Router;
use Framework\Middleware\AuthenticationMiddleware;

/**
 * Authentication Routes
 * 
 * Public authentication endpoints
 */

return function (Router $router) {
    $router->addGroup('/api/auth', function (Router $router) {
        $router->post('/login', 'AuthController@login')
            ->name('auth.login');
        
        $router->post('/register', 'AuthController@register')
            ->name('auth.register');
        
        $router->post('/refresh', 'AuthController@refresh')
            ->name('auth.refresh');
        
        // Logout requires authentication
        $router->post('/logout', 'AuthController@logout', [AuthenticationMiddleware::class])
            ->name('auth.logout');
    });
};
