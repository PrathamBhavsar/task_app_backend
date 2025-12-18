<?php

declare(strict_types=1);

namespace Framework\Container;

abstract class ServiceProvider
{
    public function __construct(protected Container $container)
    {
    }

    abstract public function register(): void;

    public function boot(): void
    {
        // Override in subclasses if needed
    }
}
