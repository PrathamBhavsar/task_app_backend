# Rate Limiting System

The rate limiting system provides protection against API abuse by limiting the number of requests a client can make within a time window.

## Features

- Redis-backed storage for distributed rate limiting
- In-memory fallback when Redis is unavailable
- Per-IP and per-user rate limiting
- Configurable limits per route or route group
- Standard rate limit headers (X-RateLimit-*)
- 429 Too Many Requests response with Retry-After header

## Configuration

Rate limiting is configured in `config/ratelimit.php`:

```php
return [
    'enabled' => true,
    'max_requests' => 60,
    'window' => 60, // seconds
    
    // Per-route overrides
    'routes' => [
        'POST /api/auth/login' => ['max_requests' => 5, 'window' => 60],
    ],
    
    // Per-group overrides
    'groups' => [
        'auth' => ['max_requests' => 10, 'window' => 60],
    ],
];
```

## Usage

### Global Rate Limiting

Apply rate limiting to all routes:

```php
$pipeline = new MiddlewarePipeline();
$pipeline->pipe(new RateLimitMiddleware($rateLimiter, $config));
```

### Per-Route Rate Limiting

Apply custom limits to specific routes:

```php
$router->addRoute(
    'POST',
    '/api/auth/login',
    'AuthController@login',
    [new RateLimitMiddleware($rateLimiter, $config, 5, 60)] // 5 requests per 60 seconds
);
```

### Per-Group Rate Limiting

Apply limits to route groups:

```php
$router->addGroup('/api/auth', function($router) {
    $router->addRoute('POST', '/login', 'AuthController@login');
    $router->addRoute('POST', '/register', 'AuthController@register');
}, [new RateLimitMiddleware($rateLimiter, $config, 10, 60)]);
```

## Response Headers

The middleware adds the following headers to all responses:

- `X-RateLimit-Limit`: Maximum number of requests allowed
- `X-RateLimit-Remaining`: Number of requests remaining in the current window
- `X-RateLimit-Reset`: Unix timestamp when the rate limit resets

When rate limit is exceeded (429 response):

- `Retry-After`: Number of seconds to wait before retrying

## Rate Limiting Strategy

1. **Authenticated Users**: Rate limited by user ID (from JWT token)
2. **Anonymous Users**: Rate limited by IP address
3. **Behind Proxy**: Supports X-Forwarded-For and X-Real-IP headers

## Example Response

### Successful Request

```http
HTTP/1.1 200 OK
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699564800
Content-Type: application/json

{
  "success": true,
  "data": { ... }
}
```

### Rate Limit Exceeded

```http
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1699564800
Retry-After: 30
Content-Type: application/json

{
  "success": false,
  "error": {
    "message": "Too Many Requests",
    "code": "RATE_LIMIT_EXCEEDED",
    "retry_after": 30
  }
}
```

## Redis Setup

The rate limiter uses Redis for distributed rate limiting. Configure Redis connection in `.env`:

```env
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_RATELIMIT_DB=1
```

If Redis is not available, the system falls back to in-memory storage (not suitable for production with multiple servers).

## Testing

To test rate limiting:

```php
// Make multiple requests rapidly
for ($i = 0; $i < 65; $i++) {
    $response = $client->get('/api/endpoint');
    
    if ($i < 60) {
        assert($response->getStatusCode() === 200);
    } else {
        assert($response->getStatusCode() === 429);
        assert($response->hasHeader('Retry-After'));
    }
}
```
