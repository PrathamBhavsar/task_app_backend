<?php

declare(strict_types=1);

namespace Framework\Routing;

use Framework\Http\Request;

/**
 * Router with API versioning support
 * 
 * Extends the base Router to add version-specific route registration
 * and automatic version prefix handling.
 */
class VersionedRouter extends Router
{
    private array $versions = [];
    private ?string $defaultVersion = null;
    private ?string $currentVersion = null;

    /**
     * Register an API version
     * 
     * @param string $version Version identifier (e.g., "v1", "v2")
     * @param bool $isDeprecated Whether this version is deprecated
     * @param string|null $deprecationMessage Custom deprecation message
     * @param string|null $sunsetDate ISO 8601 date when version will be removed
     * @return ApiVersion
     */
    public function registerVersion(
        string $version,
        bool $isDeprecated = false,
        ?string $deprecationMessage = null,
        ?string $sunsetDate = null
    ): ApiVersion {
        $apiVersion = new ApiVersion($version, $isDeprecated, $deprecationMessage, $sunsetDate);
        $this->versions[$version] = $apiVersion;
        
        return $apiVersion;
    }

    /**
     * Set the default API version to use when no version is specified
     * 
     * @param string $version Version identifier
     * @return void
     * @throws \InvalidArgumentException If version not registered
     */
    public function setDefaultVersion(string $version): void
    {
        if (!isset($this->versions[$version])) {
            throw new \InvalidArgumentException("Version '{$version}' is not registered");
        }
        
        $this->defaultVersion = $version;
    }

    /**
     * Get the default version
     */
    public function getDefaultVersion(): ?string
    {
        return $this->defaultVersion;
    }

    /**
     * Register routes for a specific API version
     * 
     * @param string $version Version identifier
     * @param callable $callback Callback that receives the router
     * @param array $middleware Additional middleware for this version
     * @return void
     * @throws \InvalidArgumentException If version not registered
     */
    public function version(string $version, callable $callback, array $middleware = []): void
    {
        if (!isset($this->versions[$version])) {
            throw new \InvalidArgumentException("Version '{$version}' must be registered before use");
        }

        $previousVersion = $this->currentVersion;
        $this->currentVersion = $version;

        // Add version prefix as a group
        $this->addGroup('/' . $version, $callback, $middleware);

        $this->currentVersion = $previousVersion;
    }

    /**
     * Get all registered versions
     * 
     * @return array<string, ApiVersion>
     */
    public function getVersions(): array
    {
        return $this->versions;
    }

    /**
     * Get a specific version
     */
    public function getVersion(string $version): ?ApiVersion
    {
        return $this->versions[$version] ?? null;
    }

    /**
     * Check if a version is registered
     */
    public function hasVersion(string $version): bool
    {
        return isset($this->versions[$version]);
    }

    /**
     * Match a request to a route, with version fallback support
     * 
     * @param Request $request
     * @return RouteMatch|null
     */
    public function match(Request $request): ?RouteMatch
    {
        $path = $request->getPath();
        
        // Try to match with the requested path first
        $match = parent::match($request);
        
        if ($match !== null) {
            // Extract version from the matched route if present
            $version = $this->extractVersionFromPath($path);
            if ($version !== null && isset($this->versions[$version])) {
                $match->setVersion($this->versions[$version]);
            }
            return $match;
        }

        // If no match and default version is set, try with default version prefix
        if ($this->defaultVersion !== null && !$this->hasVersionPrefix($path)) {
            $versionedPath = '/' . $this->defaultVersion . $path;
            $versionedRequest = $request->withPath($versionedPath);
            
            $match = parent::match($versionedRequest);
            
            if ($match !== null) {
                $match->setVersion($this->versions[$this->defaultVersion]);
                return $match;
            }
        }

        return null;
    }

    /**
     * Extract version from path (e.g., "/v1/api/clients" -> "v1")
     */
    private function extractVersionFromPath(string $path): ?string
    {
        if (preg_match('#^/(v\d+)/#', $path, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Check if path already has a version prefix
     */
    private function hasVersionPrefix(string $path): bool
    {
        return preg_match('#^/v\d+/#', $path) === 1;
    }

    /**
     * Get the current version being registered
     */
    public function getCurrentVersion(): ?string
    {
        return $this->currentVersion;
    }
}
