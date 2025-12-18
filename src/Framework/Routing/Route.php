<?php

declare(strict_types=1);

namespace Framework\Routing;

class Route
{
    private ?string $routeName = null;
    private ?\Closure $nameRegistrationCallback = null;

    public function __construct(
        public readonly string $method,
        public readonly string $pattern,
        public readonly string $handler,
        public readonly array $middleware = [],
        ?string $name = null
    ) {
        $this->routeName = $name;
    }

    /**
     * Set the route name (fluent interface)
     * 
     * @param string $name Route name
     * @return self
     */
    public function name(string $name): self
    {
        $this->routeName = $name;
        
        // Call the registration callback if set
        if ($this->nameRegistrationCallback !== null) {
            ($this->nameRegistrationCallback)($name, $this);
        }
        
        return $this;
    }

    /**
     * Set the callback for registering named routes
     * 
     * @param \Closure $callback Callback function
     * @return void
     */
    public function setNameRegistrationCallback(\Closure $callback): void
    {
        $this->nameRegistrationCallback = $callback;
    }

    /**
     * Get the route name
     * 
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->routeName;
    }

    /**
     * Magic getter for backward compatibility with readonly name property
     */
    public function __get(string $property): mixed
    {
        if ($property === 'name') {
            return $this->routeName;
        }
        
        throw new \InvalidArgumentException("Property '{$property}' does not exist");
    }

    public function matches(string $method, string $path): bool
    {
        if ($this->method !== $method) {
            return false;
        }

        $pattern = $this->convertPatternToRegex($this->pattern);
        
        return preg_match($pattern, $path) === 1;
    }

    public function extractParams(string $path): array
    {
        $pattern = $this->convertPatternToRegex($this->pattern);
        
        if (preg_match($pattern, $path, $matches)) {
            // Remove numeric keys, keep only named parameters
            return array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);
        }

        return [];
    }

    private function convertPatternToRegex(string $pattern): string
    {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $pattern);
        
        // Convert {param} to named capture group
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $pattern);
        
        // Convert {param?} to optional named capture group
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^\/]+)?', $pattern);
        
        // Convert {param:regex} to named capture group with custom regex
        $pattern = preg_replace('/\{(\w+):([^\}]+)\}/', '(?P<$1>$2)', $pattern);
        
        return '/^' . $pattern . '$/';
    }
}
