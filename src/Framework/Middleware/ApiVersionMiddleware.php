<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Routing\RouteMatch;

/**
 * Middleware to add API version deprecation headers
 * 
 * Adds deprecation warnings and sunset headers for deprecated API versions
 */
class ApiVersionMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        // Get the route match from request attributes
        $routeMatch = $request->getAttribute('route_match');
        
        if (!$routeMatch instanceof RouteMatch || !$routeMatch->hasVersion()) {
            return $response;
        }

        $version = $routeMatch->getVersion();

        // Add deprecation header if version is deprecated
        if ($version->isDeprecated()) {
            $deprecationHeader = $version->getDeprecationHeader();
            if ($deprecationHeader !== null) {
                $response = $response->withHeader('Deprecation', 'true');
                $response = $response->withHeader('X-API-Deprecation-Info', $deprecationHeader);
            }

            // Add Sunset header if sunset date is specified (RFC 8594)
            $sunsetHeader = $version->getSunsetHeader();
            if ($sunsetHeader !== null) {
                $response = $response->withHeader('Sunset', $sunsetHeader);
            }
        }

        // Add API version header for all versioned routes
        $response = $response->withHeader('X-API-Version', $version->version);

        return $response;
    }
}
