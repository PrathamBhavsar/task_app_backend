<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Error\UnauthorizedException;
use Infrastructure\Auth\JwtService;

class AuthenticationMiddleware implements MiddlewareInterface
{
    private JwtService $jwtService;
    private array $publicRoutes;

    public function __construct(JwtService $jwtService, array $publicRoutes = [])
    {
        $this->jwtService = $jwtService;
        $this->publicRoutes = $publicRoutes;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Check if route is public
        if ($this->isPublicRoute($request)) {
            return $handler->handle($request);
        }

        // Extract token from Authorization header
        $token = $this->extractToken($request);
        
        if ($token === null) {
            throw new UnauthorizedException('Missing authentication token');
        }

        try {
            // Verify token with signature, issuer, and audience checks
            $payload = $this->jwtService->verifyToken($token);
            
            // Extract user information and add to request attributes
            $request = $this->addUserToRequest($request, $payload);
            
            // Continue with the request
            return $handler->handle($request);
            
        } catch (\Exception $e) {
            throw new UnauthorizedException('Invalid or expired token: ' . $e->getMessage());
        }
    }

    /**
     * Extract JWT token from Authorization header
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->getHeader('Authorization');
        
        if ($authHeader === null) {
            return null;
        }

        // Check for Bearer token format
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        // Extract token after "Bearer "
        $token = substr($authHeader, 7);
        
        return !empty($token) ? $token : null;
    }

    /**
     * Add user information to request attributes
     */
    private function addUserToRequest(Request $request, array $payload): Request
    {
        // Add user ID
        if (isset($payload['sub'])) {
            $request = $request->withAttribute('user_id', $payload['sub']);
        }

        // Add email if present
        if (isset($payload['email'])) {
            $request = $request->withAttribute('user_email', $payload['email']);
        }

        // Add full payload for additional claims
        $request = $request->withAttribute('jwt_payload', $payload);

        return $request;
    }

    /**
     * Check if the current route is public (bypasses authentication)
     */
    private function isPublicRoute(Request $request): bool
    {
        $path = $request->getPath();
        
        foreach ($this->publicRoutes as $publicRoute) {
            // Exact match
            if ($path === $publicRoute) {
                return true;
            }
            
            // Pattern match (supports wildcards)
            if (str_contains($publicRoute, '*')) {
                // Escape special regex characters except *
                $pattern = preg_quote($publicRoute, '/');
                // Replace escaped \* with .* for wildcard matching
                $pattern = str_replace('\*', '.*', $pattern);
                if (preg_match('/^' . $pattern . '$/', $path)) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
