# Routes Directory

This directory contains route configuration files organized by feature or resource group.

## File Organization

- `system.php` - System routes (health checks, metrics)
- `auth.php` - Authentication routes (login, register, logout)
- `api.php` - Protected API routes (clients, projects, tasks, bills, users)

## Route Definition Format

Each route file should return a callable that accepts a `Router` instance:

```php
<?php

use Framework\Routing\Router;
use Framework\Middleware\AuthenticationMiddleware;

return function (Router $router) {
    // Define routes here
    $router->get('/example', 'ExampleController@index')
        ->name('example.index');
};
```

## Route Features

### Route Grouping

Group routes with a common prefix and shared middleware:

```php
$router->addGroup('/api/v1', function (Router $router) {
    $router->get('/users', 'UserController@index');
    $router->get('/posts', 'PostController@index');
}, [AuthenticationMiddleware::class]);
```

### Named Routes

Assign names to routes for URL generation:

```php
$router->get('/users/{id}', 'UserController@show')
    ->name('users.show');

// Generate URL
$url = $router->url('users.show', ['id' => 123]);
// Result: /users/123
```

### Route Parameters

Define route parameters with optional constraints:

```php
// Simple parameter
$router->get('/users/{id}', 'UserController@show');

// Parameter with regex constraint
$router->get('/users/{id:\d+}', 'UserController@show');

// Optional parameter
$router->get('/posts/{slug?}', 'PostController@show');
```

### Middleware

Apply middleware to individual routes or groups:

```php
// Route-specific middleware
$router->get('/admin', 'AdminController@index', [
    AuthenticationMiddleware::class,
    AdminMiddleware::class
]);

// Group middleware
$router->addGroup('/api', function (Router $router) {
    // All routes here have authentication
}, [AuthenticationMiddleware::class]);
```

## Loading Routes

Routes are automatically loaded by the `RoutingServiceProvider` during application bootstrap.

### Single File Loading

Configure in `config/app.php`:

```php
'routes_path' => __DIR__ . '/routes.php',
```

### Directory Loading

Configure in `config/app.php`:

```php
'routes_path' => __DIR__ . '/routes',
```

All `.php` files in the directory will be loaded automatically.

## Best Practices

1. **Organize by Feature**: Group related routes in the same file
2. **Use Named Routes**: Always name routes for easier URL generation
3. **Apply Constraints**: Use regex constraints for parameters (e.g., `{id:\d+}`)
4. **Group Middleware**: Apply shared middleware at the group level
5. **Document Routes**: Add comments to explain complex route logic
6. **Consistent Naming**: Use resource.action format (e.g., `users.index`, `posts.show`)

## Example: Resource Routes

```php
$router->addGroup('/api/posts', function (Router $router) {
    $router->get('', 'PostController@index')
        ->name('posts.index');
    
    $router->post('', 'PostController@store')
        ->name('posts.store');
    
    $router->get('/{id:\d+}', 'PostController@show')
        ->name('posts.show');
    
    $router->put('/{id:\d+}', 'PostController@update')
        ->name('posts.update');
    
    $router->delete('/{id:\d+}', 'PostController@destroy')
        ->name('posts.destroy');
}, [AuthenticationMiddleware::class]);
```
