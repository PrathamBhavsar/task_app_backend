<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\RateLimit\RateLimiter;
use Framework\Error\RateLimitException;

class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimiter $limiter;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(
        RateLimiter $limiter,
        int $maxAttempts = 60,
        int $decaySeconds = 60
    ) {
        $this->limiter = $limiter;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);
            throw new RateLimitException(
                retryAfter: $retryAfter,
                message: 'Too many requests. Please try again later.'
            );
        }

        $this->limiter->hit($key, $this->decaySeconds);
        $response = $handler->handle($request);

        return $this->addRateLimitHeaders($response, $key);
    }

    private function resolveRequestSignature(Request $request): string
    {
        // Use user ID if authenticated, otherwise use IP
        $userId = $request->getAttribute('user_id');
        
        if ($userId) {
            return 'user:' . $userId;
        }

        // Fallback to IP address
        $ip = $request->server['REMOTE_ADDR'] ?? 'unknown';
        return 'ip:' . $ip;
    }

    private function addRateLimitHeaders(Response $response, string $key): Response
    {
        $response = $response->withHeader(
            'X-RateLimit-Limit',
            (string) $this->maxAttempts
        );

        $response = $response->withHeader(
            'X-RateLimit-Remaining',
            (string) $this->limiter->remaining($key, $this->maxAttempts)
        );

        $resetTime = time() + $this->limiter->availableIn($key);
        $response = $response->withHeader(
            'X-RateLimit-Reset',
            (string) $resetTime
        );

        return $response;
    }
}
