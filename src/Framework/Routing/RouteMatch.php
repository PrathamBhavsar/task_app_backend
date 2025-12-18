<?php

declare(strict_types=1);

namespace Framework\Routing;

class RouteMatch
{
    private ?ApiVersion $version = null;

    public function __construct(
        public readonly Route $route,
        public readonly array $params,
        public readonly array $queryParams
    ) {}

    /**
     * Set the API version for this route match
     */
    public function setVersion(ApiVersion $version): void
    {
        $this->version = $version;
    }

    /**
     * Get the API version for this route match
     */
    public function getVersion(): ?ApiVersion
    {
        return $this->version;
    }

    /**
     * Check if this route match has a version
     */
    public function hasVersion(): bool
    {
        return $this->version !== null;
    }
}
