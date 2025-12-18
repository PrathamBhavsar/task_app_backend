<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;

class CorsMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;
    private array $exposedHeaders;
    private bool $allowCredentials;
    private int $maxAge;

    public function __construct(array $config)
    {
        $this->allowedOrigins = $config['allowed_origins'] ?? ['*'];
        $this->allowedMethods = $config['allowed_methods'] ?? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $this->allowedHeaders = $config['allowed_headers'] ?? ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin'];
        $this->exposedHeaders = $config['exposed_headers'] ?? [];
        $this->allowCredentials = $config['allow_credentials'] ?? false;
        $this->maxAge = $config['max_age'] ?? 86400;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get the origin from the request
        $origin = $request->getHeader('Origin');
        
        // Handle preflight OPTIONS request
        if ($request->method === 'OPTIONS') {
            return $this->handlePreflightRequest($origin);
        }
        
        // Process the actual request
        $response = $handler->handle($request);
        
        // Add CORS headers to the response
        return $this->addCorsHeaders($response, $origin);
    }

    private function handlePreflightRequest(?string $origin): Response
    {
        $response = new Response(
            body: '',
            status: 200,
            headers: []
        );
        
        return $this->addCorsHeaders($response, $origin);
    }

    private function addCorsHeaders(Response $response, ?string $origin): Response
    {
        // Determine if the origin is allowed
        $allowedOrigin = $this->getAllowedOrigin($origin);
        
        if ($allowedOrigin !== null) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $allowedOrigin);
        }
        
        // Add allowed methods
        $response = $response->withHeader(
            'Access-Control-Allow-Methods',
            implode(', ', $this->allowedMethods)
        );
        
        // Add allowed headers
        $response = $response->withHeader(
            'Access-Control-Allow-Headers',
            implode(', ', $this->allowedHeaders)
        );
        
        // Add exposed headers if configured
        if (!empty($this->exposedHeaders)) {
            $response = $response->withHeader(
                'Access-Control-Expose-Headers',
                implode(', ', $this->exposedHeaders)
            );
        }
        
        // Add credentials support if enabled
        if ($this->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }
        
        // Add max age for preflight cache
        $response = $response->withHeader('Access-Control-Max-Age', (string) $this->maxAge);
        
        return $response;
    }

    private function getAllowedOrigin(?string $origin): ?string
    {
        // If no origin header, don't set CORS headers
        if ($origin === null) {
            return null;
        }
        
        // If wildcard is allowed, return it
        if (in_array('*', $this->allowedOrigins, true)) {
            // Note: When credentials are allowed, we can't use wildcard
            // We must return the specific origin
            if ($this->allowCredentials) {
                return $origin;
            }
            return '*';
        }
        
        // Check if the origin is in the allowed list
        if (in_array($origin, $this->allowedOrigins, true)) {
            return $origin;
        }
        
        // Origin not allowed
        return null;
    }
}
