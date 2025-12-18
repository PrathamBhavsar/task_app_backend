# HTTP Caching System

This caching system provides comprehensive HTTP caching support with ETag generation, Last-Modified headers, and cache invalidation mechanisms.

## Features

- **ETag Generation**: Automatic ETag generation for response content
- **Conditional Requests**: Support for `If-None-Match` and `If-Modified-Since` headers
- **304 Not Modified**: Automatic 304 responses for unchanged resources
- **Cache-Control Headers**: Configurable cache control directives
- **Last-Modified Support**: Time-based caching with Last-Modified headers
- **Cache Invalidation**: Multiple invalidation strategies (key, pattern, tag)
- **Multiple Backends**: Redis and in-memory array storage

## Components

### CacheManager

The main class for managing HTTP caching operations.

```php
use Framework\Cache\CacheManager;
use Framework\Cache\CacheFactory;
use Framework\Config\Config;

// Create cache store
$config = new Config(['cache' => require 'config/cache.php']);
$store = CacheFactory::create($config);

// Create cache manager
$cacheManager = new CacheManager($store);

// Generate ETag
$etag = $cacheManager->generateETag($responseBody);

// Check if not modified
if ($cacheManager->isNotModified($request, $etag)) {
    return new Response(null, 304, ['ETag' => $etag]);
}

// Set cache headers
$response = $cacheManager->setCacheHeaders($response, [
    'max_age' => 3600,
    'must_revalidate' => true,
    'vary' => ['Accept', 'Accept-Encoding']
]);
```

### CacheMiddleware

Middleware that automatically handles HTTP caching for GET and HEAD requests.

```php
use Framework\Middleware\CacheMiddleware;
use Framework\Cache\CacheManager;

$middleware = new CacheMiddleware($cacheManager, [
    'max_age' => 3600,
    'enable_etag' => true,
    'enable_last_modified' => false,
    'vary' => ['Accept', 'Accept-Encoding']
]);

// Add to middleware pipeline
$pipeline->pipe($middleware);
```

### Cache Stores

#### RedisStore

Redis-backed cache storage for distributed systems.

```php
use Framework\Cache\RedisStore;
use Redis;

$redis = new Redis();
$redis->connect('localhost', 6379);

$store = new RedisStore($redis, 'api_cache');
```

#### ArrayStore

In-memory cache storage for development and testing.

```php
use Framework\Cache\ArrayStore;

$store = new ArrayStore();
```

## Configuration

Configure caching in `config/cache.php`:

```php
return [
    'driver' => $_ENV['CACHE_DRIVER'] ?? 'redis',
    'prefix' => $_ENV['CACHE_PREFIX'] ?? 'api_cache',
    
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? 'localhost',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => 0,
    ],
];
```

## Usage Examples

### Basic ETag Caching

```php
// In your controller
public function show(Request $request, int $id): Response
{
    $client = $this->clientRepository->find($id);
    
    $response = new Response($client, 200);
    
    // Generate ETag
    $etag = $this->cacheManager->generateETag($client);
    
    // Check if client has cached version
    if ($this->cacheManager->isNotModified($request, $etag)) {
        return new Response(null, 304, ['ETag' => $etag]);
    }
    
    // Add cache headers
    $response = $response->withHeader('ETag', $etag);
    $response = $this->cacheManager->setCacheHeaders($response, [
        'max_age' => 3600
    ]);
    
    return $response;
}
```

### Last-Modified Caching

```php
public function show(Request $request, int $id): Response
{
    $client = $this->clientRepository->find($id);
    $lastModified = $client->getUpdatedAt();
    
    // Check if client has cached version
    if ($this->cacheManager->checkLastModified($request, $lastModified)) {
        return new Response(null, 304, [
            'Last-Modified' => $lastModified->format('D, d M Y H:i:s') . ' GMT'
        ]);
    }
    
    $response = new Response($client, 200);
    $response = $this->cacheManager->setCacheHeaders($response, [
        'max_age' => 3600,
        'last_modified' => $lastModified
    ]);
    
    return $response;
}
```

### Cache Invalidation

```php
// Invalidate specific cache key
$this->cacheManager->invalidate('client:123');

// Invalidate by pattern (if supported by store)
$this->cacheManager->invalidatePattern('client:*');

// Invalidate by tag
$this->cacheManager->invalidateTag('clients');
```

### Using Cache Store Directly

```php
// Store data in cache
$this->cacheManager->put('client:123', $client, 3600);

// Retrieve from cache
$client = $this->cacheManager->get('client:123');

// Check if exists
if ($this->cacheManager->has('client:123')) {
    // ...
}

// Remember pattern (get or compute and store)
$client = $this->cacheManager->remember('client:123', 3600, function() use ($id) {
    return $this->clientRepository->find($id);
});
```

### Middleware with Cache Keys

```php
// In your controller, set cache key as request attribute
public function index(Request $request): Response
{
    $cacheKey = 'clients:list:' . md5(json_encode($request->query));
    $request = $request->withAttribute('cache_key', $cacheKey);
    
    // Middleware will automatically cache the response
    $clients = $this->clientRepository->findAll();
    
    return new Response($clients, 200);
}
```

### Advanced Cache Control

```php
$response = $this->cacheManager->setCacheHeaders($response, [
    'max_age' => 3600,           // Cache for 1 hour
    's_maxage' => 7200,          // Shared cache for 2 hours
    'must_revalidate' => true,   // Must revalidate when stale
    'vary' => ['Accept', 'Accept-Encoding'], // Vary by these headers
]);
```

### Disable Caching

```php
$response = $this->cacheManager->setCacheHeaders($response, [
    'no_cache' => true
]);
```

## Cache Headers Reference

### Cache-Control Directives

- `public`: Response can be cached by any cache
- `private`: Response can only be cached by browser
- `no-cache`: Must revalidate with server before using cached copy
- `no-store`: Must not be cached at all
- `max-age=<seconds>`: Maximum time resource is considered fresh
- `s-maxage=<seconds>`: Like max-age but only for shared caches
- `must-revalidate`: Must revalidate when stale

### ETag

Entity tag for identifying specific version of a resource.

```
ETag: "33a64df551425fcc55e4d42a148795d9f25f89d4"
```

### Last-Modified

Date and time when resource was last modified.

```
Last-Modified: Wed, 21 Oct 2015 07:28:00 GMT
```

### Vary

Indicates which request headers affect the response.

```
Vary: Accept, Accept-Encoding
```

## Best Practices

1. **Use ETags for dynamic content**: ETags work well for content that changes frequently
2. **Use Last-Modified for static content**: Last-Modified is simpler for files and static resources
3. **Set appropriate max-age**: Balance freshness with cache hit rate
4. **Use Vary header**: Ensure correct caching for content negotiation
5. **Invalidate on updates**: Always invalidate cache when resources are updated
6. **Use cache keys wisely**: Include relevant query parameters in cache keys
7. **Monitor cache hit rates**: Track effectiveness of your caching strategy

## Testing

```php
// Test ETag generation
$etag = $cacheManager->generateETag(['id' => 1, 'name' => 'Test']);
assert($etag === '"' . md5(json_encode(['id' => 1, 'name' => 'Test'])) . '"');

// Test conditional request
$request = new Request(
    method: 'GET',
    uri: '/api/clients/1',
    headers: ['If-None-Match' => $etag],
    query: [],
    body: [],
    files: [],
    server: []
);

assert($cacheManager->isNotModified($request, $etag) === true);
```

## Performance Considerations

- **Redis**: Use Redis for production environments with multiple servers
- **Array Store**: Use array store only for development/testing
- **Cache TTL**: Set appropriate TTL based on data volatility
- **Cache Size**: Monitor cache size and implement eviction policies
- **Network**: ETags reduce bandwidth but still require round-trip
- **Compression**: Combine with response compression for best results
