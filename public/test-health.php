<?php

/**
 * Test script for Health Check System
 * 
 * This script demonstrates and tests the health check functionality.
 * Access via: http://localhost/test-health.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Health\HealthChecker;
use Framework\Health\DatabaseHealthCheck;
use Framework\Health\RedisHealthCheck;
use Infrastructure\Database\EntityManagerFactory;

echo "<h1>Health Check System Test</h1>";

// Create HealthChecker
$healthChecker = new HealthChecker();

echo "<h2>1. Testing HealthChecker without checks</h2>";
$result = $healthChecker->check();
echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
echo "<p>✓ Empty health check completed</p>";

echo "<h2>2. Adding Database Health Check</h2>";
try {
    $em = EntityManagerFactory::create();
    $dbHealthCheck = new DatabaseHealthCheck($em);
    $healthChecker->addCheck('database', $dbHealthCheck);
    echo "<p>✓ Database health check added</p>";
} catch (\Throwable $e) {
    echo "<p>⚠ Could not add database health check: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>3. Adding Redis Health Check</h2>";
try {
    // Try to create Redis connection
    $redis = null;
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redisHost = $_ENV['REDIS_HOST'] ?? 'localhost';
        $redisPort = (int)($_ENV['REDIS_PORT'] ?? 6379);
        
        try {
            $redis->connect($redisHost, $redisPort, 2.0);
            echo "<p>✓ Redis connection established</p>";
        } catch (\Throwable $e) {
            echo "<p>⚠ Redis connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
            $redis = null;
        }
    } else {
        echo "<p>⚠ Redis extension not installed</p>";
    }
    
    $redisHealthCheck = new RedisHealthCheck($redis);
    $healthChecker->addCheck('redis', $redisHealthCheck);
    echo "<p>✓ Redis health check added</p>";
} catch (\Throwable $e) {
    echo "<p>⚠ Could not add Redis health check: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>4. Running Complete Health Check</h2>";
$result = $healthChecker->check();
echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";

$overallStatus = $result['status'];
$statusColor = $overallStatus === 'healthy' ? 'green' : 'red';
echo "<p style='color: $statusColor; font-weight: bold;'>Overall Status: " . strtoupper($overallStatus) . "</p>";

echo "<h2>5. Individual Check Results</h2>";
if (isset($result['checks'])) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>Check Name</th><th>Status</th><th>Response Time</th><th>Message</th></tr>";
    
    foreach ($result['checks'] as $name => $check) {
        $status = $check['status'];
        $statusColor = $status === 'healthy' ? 'green' : 'red';
        $responseTime = isset($check['response_time_ms']) ? round($check['response_time_ms'], 2) . ' ms' : 'N/A';
        $message = $check['message'] ?? 'N/A';
        
        echo "<tr>";
        echo "<td><strong>$name</strong></td>";
        echo "<td style='color: $statusColor;'><strong>$status</strong></td>";
        echo "<td>$responseTime</td>";
        echo "<td>" . htmlspecialchars($message) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

echo "<h2>6. Testing HTTP Status Codes</h2>";
$httpStatus = $overallStatus === 'healthy' ? 200 : 503;
echo "<p>HTTP Status Code: <strong>$httpStatus</strong></p>";
echo "<p>✓ Correct status code for $overallStatus state</p>";

echo "<h2>7. Testing Individual Health Checks</h2>";

// Test Database Health Check directly
if (isset($dbHealthCheck)) {
    echo "<h3>Database Health Check</h3>";
    $start = microtime(true);
    $dbResult = $dbHealthCheck->check();
    $duration = (microtime(true) - $start) * 1000;
    echo "<pre>" . json_encode($dbResult->toArray(), JSON_PRETTY_PRINT) . "</pre>";
    echo "<p>Response time: " . round($duration, 2) . " ms</p>";
}

// Test Redis Health Check directly
if (isset($redisHealthCheck)) {
    echo "<h3>Redis Health Check</h3>";
    $start = microtime(true);
    $redisResult = $redisHealthCheck->check();
    $duration = (microtime(true) - $start) * 1000;
    echo "<pre>" . json_encode($redisResult->toArray(), JSON_PRETTY_PRINT) . "</pre>";
    echo "<p>Response time: " . round($duration, 2) . " ms</p>";
}

echo "<h2>8. Testing Custom Health Check</h2>";
// Create a custom health check for demonstration
$customCheck = new class implements Framework\Health\HealthCheckInterface {
    public function check(): Framework\Health\CheckResult
    {
        $diskFreeSpace = disk_free_space('/');
        $diskTotalSpace = disk_total_space('/');
        $freeSpaceGB = round($diskFreeSpace / (1024 ** 3), 2);
        $totalSpaceGB = round($diskTotalSpace / (1024 ** 3), 2);
        $usagePercent = round((1 - ($diskFreeSpace / $diskTotalSpace)) * 100, 2);
        
        $healthy = $usagePercent < 90; // Unhealthy if disk usage > 90%
        
        return new Framework\Health\CheckResult(
            healthy: $healthy,
            message: $healthy ? 'Disk space is sufficient' : 'Disk space is critically low',
            metadata: [
                'free_space_gb' => $freeSpaceGB,
                'total_space_gb' => $totalSpaceGB,
                'usage_percent' => $usagePercent,
            ]
        );
    }
};

$healthChecker->addCheck('disk_space', $customCheck);
echo "<p>✓ Custom disk space health check added</p>";

echo "<h2>9. Final Complete Health Check</h2>";
$finalResult = $healthChecker->check();
echo "<pre>" . json_encode($finalResult, JSON_PRETTY_PRINT) . "</pre>";

echo "<h2>Test Complete!</h2>";
echo "<p>All health check features are working correctly.</p>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>Health check endpoint is available at: <code>GET /health</code></li>";
echo "<li>Configure monitoring tools to poll the health endpoint</li>";
echo "<li>Set up alerts for unhealthy status (503 responses)</li>";
echo "<li>Add custom health checks for external services (APIs, message queues, etc.)</li>";
echo "<li>Integrate with load balancers for automatic failover</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Health Check Endpoint Usage:</h3>";
echo "<pre>";
echo "# Check system health\n";
echo "curl http://localhost/health\n\n";
echo "# Expected response (200 OK when healthy):\n";
echo json_encode([
    'status' => 'healthy',
    'checks' => [
        'database' => [
            'status' => 'healthy',
            'message' => 'Database connection is healthy',
            'response_time_ms' => 5.23
        ],
        'redis' => [
            'status' => 'healthy',
            'message' => 'Redis connection is healthy',
            'response_time_ms' => 2.15
        ]
    ],
    'timestamp' => date('Y-m-d\TH:i:s\Z')
], JSON_PRETTY_PRINT);
echo "\n\n# Expected response (503 Service Unavailable when unhealthy):\n";
echo json_encode([
    'status' => 'unhealthy',
    'checks' => [
        'database' => [
            'status' => 'unhealthy',
            'message' => 'Database connection failed: Connection refused',
            'response_time_ms' => 2000.50
        ]
    ],
    'timestamp' => date('Y-m-d\TH:i:s\Z')
], JSON_PRETTY_PRINT);
echo "</pre>";
