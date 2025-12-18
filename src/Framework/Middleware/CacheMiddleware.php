<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Cache\CacheManager;

class CacheMiddleware implements MiddlewareInterface
{
    private CacheManager $cache;
    private array $options;

    public function __construct(CacheManager $cache, array $options = [])
    {
        $this->cache = $cache;
        $this->options = array_merge([
            'max_age' => 3600,
            'cacheable_methods' => ['GET', 'HEAD'],
            'cacheable_status' => [200, 203, 204, 206, 300, 301, 404, 405, 410, 414, 501],
            'enable_etag' => true,
            'enable_last_modified' => false,
            'vary' => ['Accept', 'Accept-Encoding'],
        ], $options);
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Only cache GET and HEAD requests
        if (!in_array($request->method, $this->options['cacheable_methods'])) {
            return $handler->handle($request);
        }

        // Check for cache key in request attributes (set by controller)
        $cacheKey = $request->getAttribute('cache_key');
        
        // Try to serve from cache if cache key is provided
        if ($cacheKey !== null && $this->cache->has($cacheKey)) {
            $cachedResponse = $this->cache->get($cacheKey);
            
            if ($cachedResponse instanceof Response) {
                return $cachedResponse;
            }
        }

        // Process the request
        $response = $handler->handle($request);

        // Only add cache headers for cacheable status codes
        if (!in_array($response->status, $this->options['cacheable_status'])) {
            return $response;
        }

        // Check if response explicitly disables caching
        if ($response->hasHeader('Cache-Control') && 
            str_contains($response->getHeader('Cache-Control'), 'no-cache')) {
            return $response;
        }

        // Handle ETag-based caching
        if ($this->options['enable_etag']) {
            $etag = $this->cache->generateETag($response->body);
            
            // Check if client has cached version via ETag
            if ($this->cache->isNotModified($request, $etag)) {
                return new Response(
                    body: null,
                    status: 304,
                    headers: ['ETag' => $etag]
                );
            }

            // Add ETag header
            $response = $response->withHeader('ETag', $etag);
        }

        // Handle Last-Modified based caching
        if ($this->options['enable_last_modified']) {
            $lastModified = $request->getAttribute('last_modified');
            
            if ($lastModified instanceof \DateTime) {
                // Check if client has cached version via Last-Modified
                if ($this->cache->checkLastModified($request, $lastModified)) {
                    $headers = ['Last-Modified' => $lastModified->format('D, d M Y H:i:s') . ' GMT'];
                    
                    if ($this->options['enable_etag']) {
                        $headers['ETag'] = $this->cache->generateETag($response->body);
                    }
                    
                    return new Response(
                        body: null,
                        status: 304,
                        headers: $headers
                    );
                }

                // Add Last-Modified to options for setCacheHeaders
                $this->options['last_modified'] = $lastModified;
            }
        }

        // Add cache control headers
        $response = $this->cache->setCacheHeaders($response, $this->options);

        // Store in cache if cache key is provided
        if ($cacheKey !== null) {
            $cacheTtl = $this->options['max_age'] ?? 3600;
            $this->cache->put($cacheKey, $response, $cacheTtl);
        }

        return $response;
    }
}
