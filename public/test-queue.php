<?php

declare(strict_types=1);

/**
 * Queue System Test Script
 * 
 * This script demonstrates the queue system functionality.
 * 
 * Usage:
 *   1. Start the worker: php bin/queue-worker.php
 *   2. Run this script: php public/test-queue.php
 *   3. Watch the worker process the jobs
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\Config;
use Framework\Config\EnvLoader;
use Framework\Queue\QueueFactory;
use Framework\Queue\ExampleJob;

// Load environment
$envLoader = new EnvLoader();
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envLoader->load($envPath);
}

// Create config
$config = new Config();

echo "===========================================\n";
echo "Queue System Test\n";
echo "===========================================\n\n";

try {
    // Create queue manager
    $queueManager = QueueFactory::create($config);
    
    echo "✓ Queue manager created\n";
    echo "  Driver: " . $config->getString('queue.driver', 'redis') . "\n\n";
    
    // Test 1: Push immediate jobs
    echo "Test 1: Dispatching immediate jobs\n";
    echo "-------------------------------------------\n";
    
    $jobIds = [];
    for ($i = 1; $i <= 3; $i++) {
        $jobId = $queueManager->push(
            new ExampleJob("Task #{$i}", 1),
            'default'
        );
        $jobIds[] = $jobId;
        echo "  ✓ Job #{$i} dispatched (ID: {$jobId})\n";
    }
    
    echo "\n";
    
    // Test 2: Push delayed jobs
    echo "Test 2: Dispatching delayed jobs\n";
    echo "-------------------------------------------\n";
    
    $delayedJobId = $queueManager->later(
        new ExampleJob("Delayed Task", 1),
        10 // 10 seconds delay
    );
    echo "  ✓ Delayed job dispatched (ID: {$delayedJobId})\n";
    echo "    Will execute in 10 seconds\n\n";
    
    // Test 3: Push to different queue
    echo "Test 3: Dispatching to custom queue\n";
    echo "-------------------------------------------\n";
    
    $customJobId = $queueManager->push(
        new ExampleJob("Custom Queue Task", 1),
        'custom'
    );
    echo "  ✓ Job dispatched to 'custom' queue (ID: {$customJobId})\n";
    echo "    Run worker with: php bin/queue-worker.php custom\n\n";
    
    // Test 4: Check job status
    echo "Test 4: Checking job status\n";
    echo "-------------------------------------------\n";
    
    sleep(1); // Give worker a moment to process
    
    foreach ($jobIds as $index => $jobId) {
        $status = $queueManager->getJobStatus($jobId);
        if ($status) {
            echo "  Job #{$index} ({$jobId}):\n";
            echo "    Status: {$status['status']}\n";
            echo "    Updated: " . date('Y-m-d H:i:s', $status['updated_at']) . "\n";
            if (isset($status['error'])) {
                echo "    Error: {$status['error']}\n";
            }
        } else {
            echo "  Job #{$index} ({$jobId}): Not yet processed or status expired\n";
        }
    }
    
    echo "\n";
    
    // Summary
    echo "===========================================\n";
    echo "Test Summary\n";
    echo "===========================================\n";
    echo "✓ Dispatched 3 immediate jobs to 'default' queue\n";
    echo "✓ Dispatched 1 delayed job (10s delay)\n";
    echo "✓ Dispatched 1 job to 'custom' queue\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "1. Make sure worker is running: php bin/queue-worker.php\n";
    echo "2. Watch the worker console for job processing\n";
    echo "3. For custom queue: php bin/queue-worker.php custom\n";
    echo "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Test completed successfully!\n";
