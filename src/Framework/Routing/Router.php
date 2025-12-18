<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Http\Request;

class Router
{
    private array $routes = [];
    private array $groups = [];
    private array $namedRoutes = [];

    public function addRoute(
        string $method,
        string $pattern,
        string $handler,
        array $middleware = [],
        ?string $name = null
    ): Route {
        // Apply group prefix if within a group
        if ($this->currentGroupPrefix !== null) {
            $pattern = $this->currentGroupPrefix . $pattern;
        }
        
        // Merge group middleware with route middleware
        if (!empty($this->currentGroupMiddleware)) {
            $middleware = array_merge($this->currentGroupMiddleware, $middleware);
        }
        
        $route = new Route($method, $pattern, $handler, $middleware, $name);
        $this->routes[] = $route;
        
        // Set callback for fluent name() method
        $route->setNameRegistrationCallback(function (string $name, Route $route) {
            $this->registerNamedRoute($name, $route);
        });
        
        // Store named route for URL generation if name provided
        if ($name !== null) {
            $this->registerNamedRoute($name, $route);
        }
        
        return $route;
    }

    /**
     * Register a named route
     * 
     * @param string $name Route name
     * @param Route $route Route instance
     * @return void
     */
    public function registerNamedRoute(string $name, Route $route): void
    {
        $this->namedRoutes[$name] = $route;
    }

    public function get(string $pattern, string $handler, array $middleware = []): Route
    {
        return $this->addRoute('GET', $pattern, $handler, $middleware);
    }

    public function post(string $pattern, string $handler, array $middleware = []): Route
    {
        return $this->addRoute('POST', $pattern, $handler, $middleware);
    }

    public function put(string $pattern, string $handler, array $middleware = []): Route
    {
        return $this->addRoute('PUT', $pattern, $handler, $middleware);
    }

    public function delete(string $pattern, string $handler, array $middleware = []): Route
    {
        return $this->addRoute('DELETE', $pattern, $handler, $middleware);
    }

    public function patch(string $pattern, string $handler, array $middleware = []): Route
    {
        return $this->addRoute('PATCH', $pattern, $handler, $middleware);
    }

    public function addGroup(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->currentGroupPrefix ?? '';
        $previousMiddleware = $this->currentGroupMiddleware ?? [];

        $this->currentGroupPrefix = $previousPrefix . $prefix;
        $this->currentGroupMiddleware = array_merge($previousMiddleware, $middleware);

        $callback($this);

        $this->currentGroupPrefix = $previousPrefix;
        $this->currentGroupMiddleware = $previousMiddleware;
    }

    private ?string $currentGroupPrefix = null;
    private array $currentGroupMiddleware = [];

    public function match(Request $request): ?RouteMatch
    {
        $method = $request->method;
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route->matches($method, $path)) {
                $params = $route->extractParams($path);
                
                return new RouteMatch(
                    route: $route,
                    params: $params,
                    queryParams: $request->query
                );
            }
        }

        return null;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Generate URL for a named route
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @param array $queryParams Query parameters
     * @return string Generated URL
     * @throws \InvalidArgumentException If route name not found
     */
    public function url(string $name, array $params = [], array $queryParams = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '{$name}' not found");
        }

        $route = $this->namedRoutes[$name];
        $url = $route->pattern;

        // Replace route parameters
        foreach ($params as $key => $value) {
            // Handle {param}, {param?}, and {param:regex} patterns
            $url = preg_replace(
                '/\{' . preg_quote($key, '/') . '(\?|:[^\}]+)?\}/',
                (string) $value,
                $url
            );
        }

        // Remove optional parameters that weren't provided
        $url = preg_replace('/\{[^\}]+\?\}/', '', $url);

        // Check if there are still unresolved required parameters
        if (preg_match('/\{([^\}]+)\}/', $url, $matches)) {
            throw new \InvalidArgumentException(
                "Missing required parameter '{$matches[1]}' for route '{$name}'"
            );
        }

        // Add query parameters
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Check if a named route exists
     * 
     * @param string $name Route name
     * @return bool
     */
    public function hasRoute(string $name): bool
    {
        return isset($this->namedRoutes[$name]);
    }

    /**
     * Get a named route
     * 
     * @param string $name Route name
     * @return Route|null
     */
    public function getRoute(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }
}
