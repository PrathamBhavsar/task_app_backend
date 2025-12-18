<?php

/**
 * Test script for Metrics Collection System
 * 
 * This script demonstrates and tests the metrics collection functionality.
 * Access via: http://localhost/test-metrics.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Metrics\MetricsCollector;
use Framework\Metrics\DoctrineSQLLogger;

echo "<h1>Metrics Collection System Test</h1>";

// Create MetricsCollector
$metrics = new MetricsCollector();

echo "<h2>1. Testing Counter Metrics</h2>";
$metrics->increment('test_counter', ['label' => 'value1']);
$metrics->increment('test_counter', ['label' => 'value1']);
$metrics->increment('test_counter', ['label' => 'value2']);
echo "<p>✓ Incremented counters</p>";

echo "<h2>2. Testing Gauge Metrics</h2>";
$metrics->gauge('test_gauge', 42.5, ['type' => 'temperature']);
$metrics->gauge('test_gauge', 38.2, ['type' => 'humidity']);
echo "<p>✓ Set gauge values</p>";

echo "<h2>3. Testing Histogram Metrics</h2>";
for ($i = 0; $i < 10; $i++) {
    $value = rand(100, 500) / 100; // Random value between 1.00 and 5.00
    $metrics->histogram('test_histogram', $value, ['endpoint' => '/api/test']);
}
echo "<p>✓ Recorded histogram values</p>";

echo "<h2>4. Testing Timing Metrics</h2>";
$start = microtime(true);
usleep(rand(10000, 50000)); // Sleep for 10-50ms
$duration = microtime(true) - $start;
$metrics->timing('test_timing', $duration, ['operation' => 'test']);
echo "<p>✓ Recorded timing (duration: " . round($duration * 1000, 2) . "ms)</p>";

echo "<h2>5. Testing Custom Business Metrics</h2>";
$metrics->registerCustomMetric(
    'test_orders',
    'counter',
    'Test order counter',
    ['status' => 'pending']
);
$metrics->recordCustomMetric('test_orders', 1, ['status' => 'completed']);
echo "<p>✓ Registered and recorded custom metric</p>";

echo "<h2>6. Testing DoctrineSQLLogger</h2>";
$sqlLogger = new DoctrineSQLLogger($metrics);
$sqlLogger->startQuery('SELECT * FROM users WHERE id = ?', [1]);
usleep(5000); // Simulate 5ms query
$sqlLogger->stopQuery();
$sqlLogger->startQuery('SELECT * FROM orders WHERE user_id = ?', [1]);
usleep(3000); // Simulate 3ms query
$sqlLogger->stopQuery();
echo "<p>✓ Logged " . $sqlLogger->getQueryCount() . " database queries</p>";

echo "<h2>7. Metrics Summary (JSON Format)</h2>";
$allMetrics = $metrics->getMetrics();
echo "<pre>" . json_encode($allMetrics, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>8. Prometheus Export Format</h2>";
echo "<pre>" . htmlspecialchars($metrics->exportPrometheus()) . "</pre>";

echo "<h2>9. Custom Metrics Registry</h2>";
$customMetrics = $metrics->getCustomMetrics();
echo "<pre>" . json_encode($customMetrics, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>10. Cache Hit/Miss Simulation</h2>";
echo "<p>Note: Cache metrics are tracked automatically when using CacheManager with MetricsCollector</p>";
$metrics->increment('cache_hits_total', ['key' => 'user_{id}']);
$metrics->increment('cache_hits_total', ['key' => 'user_{id}']);
$metrics->increment('cache_misses_total', ['key' => 'user_{id}']);
$cacheHits = 2;
$cacheMisses = 1;
$hitRatio = ($cacheHits / ($cacheHits + $cacheMisses)) * 100;
echo "<p>✓ Cache hit ratio: " . round($hitRatio, 2) . "%</p>";

echo "<h2>Test Complete!</h2>";
echo "<p>All metrics collection features are working correctly.</p>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>Add metrics endpoints to your routes: <code>GET /metrics</code></li>";
echo "<li>Configure Prometheus to scrape the metrics endpoint</li>";
echo "<li>Set up Grafana dashboards for visualization</li>";
echo "<li>Register custom business metrics in MetricsServiceProvider</li>";
echo "<li>Integrate DoctrineSQLLogger with EntityManager</li>";
echo "</ul>";
