<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Metrics\MetricsCollector;

class MetricsMiddleware implements MiddlewareInterface
{
    private MetricsCollector $metrics;

    public function __construct(MetricsCollector $metrics)
    {
        $this->metrics = $metrics;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $start = microtime(true);
        
        // Increment in-flight requests
        $this->metrics->gauge('http_requests_in_flight', 1);

        try {
            $response = $handler->handle($request);
            
            // Record success metrics
            $this->recordMetrics($request, $response, $start);
            
            return $response;
        } catch (\Throwable $e) {
            // Record failure metrics
            $this->recordMetrics($request, null, $start, $e);
            
            throw $e;
        } finally {
            // Decrement in-flight requests
            $this->metrics->gauge('http_requests_in_flight', 0);
        }
    }

    private function recordMetrics(
        Request $request,
        ?Response $response,
        float $start,
        ?\Throwable $exception = null
    ): void {
        $duration = microtime(true) - $start;
        $path = $request->getPath();
        $method = $request->method;
        $status = $response ? $response->status : 500;

        // Record request count
        $this->metrics->increment('http_requests_total', [
            'method' => $method,
            'path' => $path,
            'status' => (string) $status,
        ]);

        // Record request duration
        $this->metrics->timing('http_request_duration_seconds', $duration, [
            'method' => $method,
            'path' => $path,
        ]);

        // Record errors
        if ($exception) {
            $this->metrics->increment('http_errors_total', [
                'method' => $method,
                'path' => $path,
                'exception' => get_class($exception),
            ]);
        }
    }
}
