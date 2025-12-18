<?php

declare(strict_types=1);

namespace Framework\Cache;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Metrics\MetricsCollector;

class CacheManager
{
    private ?CacheStoreInterface $store;
    private ?MetricsCollector $metrics;

    public function __construct(?CacheStoreInterface $store = null, ?MetricsCollector $metrics = null)
    {
        $this->store = $store;
        $this->metrics = $metrics;
    }

    public function generateETag(mixed $content): string
    {
        if (is_array($content) || is_object($content)) {
            $content = json_encode($content);
        }
        
        return '"' . md5((string) $content) . '"';
    }

    public function isNotModified(Request $request, string $etag): bool
    {
        $ifNoneMatch = $request->getHeader('If-None-Match');
        
        if ($ifNoneMatch === null) {
            return false;
        }

        // Handle multiple ETags
        $clientETags = array_map('trim', explode(',', $ifNoneMatch));
        
        return in_array($etag, $clientETags, true);
    }

    public function checkLastModified(Request $request, \DateTime $lastModified): bool
    {
        $ifModifiedSince = $request->getHeader('If-Modified-Since');
        
        if ($ifModifiedSince === null) {
            return false;
        }

        $clientTime = strtotime($ifModifiedSince);
        if ($clientTime === false) {
            return false;
        }

        return $lastModified->getTimestamp() <= $clientTime;
    }

    public function setCacheHeaders(Response $response, array $options): Response
    {
        // Set Cache-Control header
        if (isset($options['max_age'])) {
            $cacheControl = 'public, max-age=' . $options['max_age'];
            
            // Add s-maxage for shared caches if specified
            if (isset($options['s_maxage'])) {
                $cacheControl .= ', s-maxage=' . $options['s_maxage'];
            }
            
            // Add must-revalidate if specified
            if (isset($options['must_revalidate']) && $options['must_revalidate']) {
                $cacheControl .= ', must-revalidate';
            }
            
            $response = $response->withHeader('Cache-Control', $cacheControl);
        } elseif (isset($options['no_cache']) && $options['no_cache']) {
            $response = $response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response = $response->withHeader('Pragma', 'no-cache');
        }

        // Set Last-Modified header
        if (isset($options['last_modified'])) {
            $lastModified = $options['last_modified'];
            if ($lastModified instanceof \DateTime) {
                $lastModified = $lastModified->format('D, d M Y H:i:s') . ' GMT';
            }
            $response = $response->withHeader('Last-Modified', $lastModified);
        }

        // Set Expires header
        if (isset($options['expires'])) {
            $expires = $options['expires'];
            if ($expires instanceof \DateTime) {
                $expires = $expires->format('D, d M Y H:i:s') . ' GMT';
            }
            $response = $response->withHeader('Expires', $expires);
        }

        // Set Vary header for content negotiation
        if (isset($options['vary'])) {
            $vary = is_array($options['vary']) ? implode(', ', $options['vary']) : $options['vary'];
            $response = $response->withHeader('Vary', $vary);
        }

        return $response;
    }

    public function invalidate(string $key): bool
    {
        if ($this->store === null) {
            return false;
        }

        return $this->store->forget($key);
    }

    public function invalidatePattern(string $pattern): bool
    {
        if ($this->store === null) {
            return false;
        }

        // For stores that support pattern-based deletion
        // This is a simplified implementation
        return $this->store->forget($pattern);
    }

    public function invalidateTag(string $tag): bool
    {
        if ($this->store === null) {
            return false;
        }

        // Invalidate all cache entries associated with a tag
        return $this->store->forget("tag:{$tag}");
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        if ($this->store === null) {
            return false;
        }

        return $this->store->put($key, $value, $ttl);
    }

    public function get(string $key): mixed
    {
        if ($this->store === null) {
            return null;
        }

        $value = $this->store->get($key);
        
        // Track cache hit/miss
        if ($this->metrics !== null) {
            if ($value !== null) {
                $this->metrics->increment('cache_hits_total', ['key' => $this->sanitizeKeyForMetrics($key)]);
            } else {
                $this->metrics->increment('cache_misses_total', ['key' => $this->sanitizeKeyForMetrics($key)]);
            }
        }

        return $value;
    }

    public function has(string $key): bool
    {
        if ($this->store === null) {
            return false;
        }

        return $this->store->has($key);
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * Sanitize cache key for use in metrics labels
     * Removes dynamic parts to avoid label explosion
     */
    private function sanitizeKeyForMetrics(string $key): string
    {
        // Remove UUIDs, IDs, and other dynamic parts
        $sanitized = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', '{uuid}', $key);
        $sanitized = preg_replace('/\d+/', '{id}', $sanitized);
        
        return $sanitized ?? $key;
    }
}
