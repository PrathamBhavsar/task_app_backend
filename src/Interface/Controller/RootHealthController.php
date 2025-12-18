<?php

declare(strict_types=1);

namespace Interface\Controller;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Health\HealthChecker;

/**
 * Controller for root health page
 * 
 * @OA\Get(
 *     path="/",
 *     summary="Root Health Page",
 *     description="HTML page displaying API health status and quick links",
 *     tags={"Health"},
 *     @OA\Response(
 *         response=200,
 *         description="Healthy API status page",
 *         @OA\MediaType(
 *             mediaType="text/html"
 *         )
 *     ),
 *     @OA\Response(
 *         response=503,
 *         description="Unhealthy API status page",
 *         @OA\MediaType(
 *             mediaType="text/html"
 *         )
 *     )
 * )
 */
class RootHealthController
{
    private HealthChecker $healthChecker;

    public function __construct(HealthChecker $healthChecker)
    {
        $this->healthChecker = $healthChecker;
    }

    /**
     * Display root health page with API status
     */
    public function index(Request $request): Response
    {
        $result = $this->healthChecker->check();
        $isHealthy = $result['status'] === 'healthy';
        
        $html = $this->getHealthPageHtml($result, $isHealthy);
        
        return new Response($html, $isHealthy ? 200 : 503, [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
    }

    private function getHealthPageHtml(array $healthData, bool $isHealthy): string
    {
        $statusColor = $isHealthy ? '#10b981' : '#ef4444';
        $statusText = $isHealthy ? 'Healthy' : 'Unhealthy';
        $statusIcon = $isHealthy ? '✓' : '✗';
        $timestamp = $healthData['timestamp'] ?? 'N/A';
        $httpStatus = $isHealthy ? '200 OK' : '503 Service Unavailable';
        
        $checksHtml = '';
        foreach ($healthData['checks'] ?? [] as $name => $check) {
            $checkStatus = $check['status'] ?? 'unknown';
            $checkColor = $checkStatus === 'healthy' ? '#10b981' : '#ef4444';
            $checkMessage = $check['message'] ?? 'No message';
            $responseTime = isset($check['response_time_ms']) ? " ({$check['response_time_ms']}ms)" : '';
            
            $checksHtml .= "
            <div class='check-item'>
                <div class='check-header'>
                    <span class='check-status' style='color: {$checkColor};'>{$checkStatus}</span>
                    <span class='check-name'>{$name}</span>
                </div>
                <div class='check-message'>{$checkMessage}{$responseTime}</div>
            </div>";
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task App API - Health Status</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .status-badge {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            background: {$statusColor}15;
            color: {$statusColor};
            border: 2px solid {$statusColor};
        }
        .status-icon {
            font-size: 24px;
            margin-right: 8px;
        }
        h1 {
            color: #1f2937;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
        }
        .info-section {
            margin-top: 30px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        .info-value {
            color: #1f2937;
            font-weight: 600;
        }
        .checks-section {
            margin-top: 30px;
        }
        .checks-title {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .check-item {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            border-left: 4px solid #e5e7eb;
        }
        .check-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .check-status {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .check-name {
            font-weight: 600;
            color: #1f2937;
            text-transform: capitalize;
        }
        .check-message {
            color: #6b7280;
            font-size: 14px;
            margin-left: 24px;
        }
        .links {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .link {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .link:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .link-secondary {
            background: #6b7280;
        }
        .link-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="status-badge">
                <span class="status-icon">{$statusIcon}</span>
                {$statusText}
            </div>
            <h1>Task App API</h1>
            <p class="subtitle">API Health Status Dashboard</p>
        </div>
        
        <div class="info-section">
            <div class="info-item">
                <span class="info-label">Status</span>
                <span class="info-value" style="color: {$statusColor};">{$statusText}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Timestamp</span>
                <span class="info-value">{$timestamp}</span>
            </div>
            <div class="info-item">
                <span class="info-label">HTTP Status</span>
                <span class="info-value">{$httpStatus}</span>
            </div>
        </div>
        
        <div class="checks-section">
            <div class="checks-title">Health Checks</div>
            {$checksHtml}
        </div>
        
        <div class="links">
            <a href="/swagger" class="link">API Documentation</a>
            <a href="/health" class="link link-secondary">JSON Health Check</a>
            <a href="/metrics" class="link link-secondary">Metrics</a>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

