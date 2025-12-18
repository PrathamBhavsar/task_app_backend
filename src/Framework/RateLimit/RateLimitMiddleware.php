<?php

declare(strict_types=1);

namespace Framework\RateLimit;

use Framework\Config\Config;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\MiddlewareInterface;
use Framework\Middleware\RequestHandler;

class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimiter $limiter;
    private Config $config;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(
        RateLimiter $limiter,
        Config $config,
        ?int $maxAttempts = null,
        ?int $decaySeconds = null
    ) {
        $this->limiter = $limiter;
        $this->config = $config;
        $this->maxAttempts = $maxAttempts ?? $config->getInt('ratelimit.max_requests', 60);
        $this->decaySeconds = $decaySeconds ?? $config->getInt('ratelimit.window', 60);
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Check if rate limiting is enabled
        if (!$this->config->getBool('ratelimit.enabled', true)) {
            return $handler->handle($request);
        }

        $key = $this->resolveRequestSignature($request);

        if ($this->limiter->tooManyAttempts($key, $this->maxAttempts)) {
            return $this->buildRateLimitResponse($key);
        }

        $this->limiter->hit($key, $this->decaySeconds);

        $response = $handler->handle($request);

        return $this->addRateLimitHeaders($response, $key);
    }

    /**
     * Resolve the request signature for rate limiting
     */
    private function resolveRequestSignature(Request $request): string
    {
        // Try to get user identifier from request attributes (set by auth middleware)
        $userId = $request->getAttribute('user_id');
        
        if ($userId) {
            return 'user:' . $userId;
        }

        // Fall back to IP address
        $ip = $this->getClientIp($request);
        
        return 'ip:' . $ip;
    }

    /**
     * Get the client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for forwarded IP (behind proxy/load balancer)
        $forwardedFor = $request->getHeader('X-Forwarded-For');
        if ($forwardedFor) {
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        $realIp = $request->getHeader('X-Real-IP');
        if ($realIp) {
            return $realIp;
        }

        return $request->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Build the rate limit exceeded response
     */
    private function buildRateLimitResponse(string $key): Response
    {
        $retryAfter = $this->limiter->availableIn($key);
        $resetTime = time() + $retryAfter;

        $body = [
            'success' => false,
            'error' => [
                'message' => 'Too Many Requests',
                'code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $retryAfter,
            ],
        ];

        return (new Response($body, 429))
            ->withHeader('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('X-RateLimit-Reset', (string) $resetTime)
            ->withHeader('Retry-After', (string) $retryAfter)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Add rate limit headers to the response
     */
    private function addRateLimitHeaders(Response $response, string $key): Response
    {
        $attempts = $this->limiter->attempts($key);
        $remaining = $this->limiter->remaining($key, $this->maxAttempts);
        $resetTime = time() + $this->decaySeconds;

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->withHeader('X-RateLimit-Remaining', (string) $remaining)
            ->withHeader('X-RateLimit-Reset', (string) $resetTime);
    }
}
