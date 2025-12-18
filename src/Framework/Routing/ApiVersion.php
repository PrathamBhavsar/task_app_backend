<?php

declare(strict_types=1);

namespace Framework\Routing;

/**
 * Represents an API version with metadata
 */
class ApiVersion
{
    public function __construct(
        public readonly string $version,
        public readonly bool $isDeprecated = false,
        public readonly ?string $deprecationMessage = null,
        public readonly ?string $sunsetDate = null
    ) {}

    /**
     * Get the version prefix for routes (e.g., "v1", "v2")
     */
    public function getPrefix(): string
    {
        return $this->version;
    }

    /**
     * Check if this version is deprecated
     */
    public function isDeprecated(): bool
    {
        return $this->isDeprecated;
    }

    /**
     * Get deprecation warning header value
     */
    public function getDeprecationHeader(): ?string
    {
        if (!$this->isDeprecated) {
            return null;
        }

        $header = 'This API version is deprecated.';
        
        if ($this->deprecationMessage !== null) {
            $header .= ' ' . $this->deprecationMessage;
        }
        
        if ($this->sunsetDate !== null) {
            $header .= ' Sunset date: ' . $this->sunsetDate;
        }

        return $header;
    }

    /**
     * Get sunset header value (RFC 8594)
     */
    public function getSunsetHeader(): ?string
    {
        return $this->sunsetDate;
    }
}
