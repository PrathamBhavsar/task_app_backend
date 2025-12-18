<?php

declare(strict_types=1);

namespace Framework\Container;

use ReflectionClass;
use ReflectionException;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, string|callable $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, string|callable $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function resolve(string $abstract): mixed
    {
        // Check if we have a singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Check if we have a binding
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            $singleton = $this->bindings[$abstract]['singleton'];

            $instance = is_callable($concrete) 
                ? $concrete($this) 
                : $this->make($concrete);

            if ($singleton) {
                $this->instances[$abstract] = $instance;
            }

            return $instance;
        }

        // Try to auto-resolve the class
        return $this->make($abstract);
    }

    public function make(string $class, array $parameters = []): object
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new ContainerException(
                "Cannot resolve class '{$class}': {$e->getMessage()}"
            );
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException(
                "Class '{$class}' is not instantiable"
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];
        
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            
            // Use provided parameter if available
            if (isset($parameters[$name])) {
                $dependencies[] = $parameters[$name];
                continue;
            }

            $type = $parameter->getType();

            if ($type === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new ContainerException(
                    "Cannot resolve parameter '{$name}' in class '{$class}'"
                );
            }

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->resolve($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new ContainerException(
                "Cannot resolve parameter '{$name}' of type '{$type}' in class '{$class}'"
            );
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}
