<?php

declare(strict_types=1);

namespace Framework\Metrics;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\MiddlewareInterface;
use Framework\Middleware\RequestHandler;

/**
 * Middleware that tracks database query metrics per request
 */
class DatabaseMetricsMiddleware implements MiddlewareInterface
{
    private MetricsCollector $metrics;
    private DoctrineSQLLogger $sqlLogger;
    private int $queryThreshold;

    public function __construct(
        MetricsCollector $metrics,
        DoctrineSQLLogger $sqlLogger,
        int $queryThreshold = 50
    ) {
        $this->metrics = $metrics;
        $this->sqlLogger = $sqlLogger;
        $this->queryThreshold = $queryThreshold;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Reset query count at the start of the request
        $initialCount = $this->sqlLogger->getQueryCount();
        
        $response = $handler->handle($request);
        
        // Calculate queries executed during this request
        $queriesExecuted = $this->sqlLogger->getQueryCount() - $initialCount;
        
        // Record query count per request
        $this->metrics->histogram('db_queries_per_request', (float) $queriesExecuted, [
            'method' => $request->method,
            'path' => $request->getPath(),
        ]);
        
        // Log warning if query count exceeds threshold
        if ($queriesExecuted > $this->queryThreshold) {
            $this->metrics->increment('db_query_threshold_exceeded', [
                'method' => $request->method,
                'path' => $request->getPath(),
                'count' => (string) $queriesExecuted,
            ]);
            
            error_log(sprintf(
                'Query count threshold exceeded: %d queries for %s %s (threshold: %d)',
                $queriesExecuted,
                $request->method,
                $request->getPath(),
                $this->queryThreshold
            ));
        }
        
        return $response;
    }
}
