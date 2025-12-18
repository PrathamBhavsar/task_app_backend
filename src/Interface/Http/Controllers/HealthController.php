<?php

declare(strict_types=1);

namespace Interface\Http\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Health\HealthChecker;

/**
 * Controller for health check endpoints
 */
class HealthController
{
    private HealthChecker $healthChecker;

    public function __construct(HealthChecker $healthChecker)
    {
        $this->healthChecker = $healthChecker;
    }

    /**
     * Check system health
     * Returns 200 for healthy, 503 for unhealthy
     */
    public function check(Request $request): Response
    {
        $result = $this->healthChecker->check();
        
        $status = $result['status'] === 'healthy' ? 200 : 503;
        
        return new Response(
            body: $result,
            status: $status,
            headers: [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
