<?php

declare(strict_types=1);

namespace Interface\Http\Controllers;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Metrics\MetricsCollector;

/**
 * Controller for exposing application metrics
 */
class MetricsController
{
    private MetricsCollector $metrics;

    public function __construct(MetricsCollector $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * Export metrics in Prometheus format
     */
    public function prometheus(Request $request): Response
    {
        $output = $this->metrics->exportPrometheus();
        
        return new Response(
            body: $output,
            status: 200,
            headers: [
                'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
            ]
        );
    }

    /**
     * Get metrics in JSON format
     */
    public function json(Request $request): Response
    {
        $metrics = $this->metrics->getMetrics();
        
        return new Response(
            body: json_encode($metrics, JSON_PRETTY_PRINT),
            status: 200,
            headers: [
                'Content-Type' => 'application/json',
            ]
        );
    }

    /**
     * Get custom business metrics
     */
    public function custom(Request $request): Response
    {
        $customMetrics = $this->metrics->getCustomMetrics();
        
        return new Response(
            body: json_encode([
                'registered_metrics' => $customMetrics,
            ], JSON_PRETTY_PRINT),
            status: 200,
            headers: [
                'Content-Type' => 'application/json',
            ]
        );
    }
}
