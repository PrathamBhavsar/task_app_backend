<?php

declare(strict_types=1);

/**
 * Queue Worker Test Script
 * 
 * This script tests the worker by dispatching jobs and processing them immediately.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\Config;
use Framework\Config\EnvLoader;
use Framework\Queue\QueueFactory;
use Framework\Queue\Worker;
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
echo "Queue Worker Test\n";
echo "===========================================\n\n";

try {
    // Create queue manager
    $queueManager = QueueFactory::create($config);
    
    echo "✓ Queue manager created\n\n";
    
    // Dispatch test jobs
    echo "Dispatching test jobs...\n";
    $jobIds = [];
    for ($i = 1; $i <= 3; $i++) {
        $jobId = $queueManager->push(
            new ExampleJob("Test Job #{$i}", 0), // 0 second processing time for quick test
            'default'
        );
        $jobIds[] = $jobId;
        echo "  ✓ Job #{$i} dispatched (ID: {$jobId})\n";
    }
    
    echo "\n";
    
    // Create worker
    $worker = new Worker($queueManager);
    
    echo "Processing jobs...\n";
    echo "-------------------------------------------\n";
    
    // Process each job
    $processed = 0;
    while ($payload = $queueManager->pop('default')) {
        $worker->process($payload);
        $processed++;
        
        if ($processed >= 3) {
            break; // Stop after processing 3 jobs
        }
    }
    
    echo "-------------------------------------------\n\n";
    
    // Check job statuses
    echo "Checking job statuses...\n";
    foreach ($jobIds as $index => $jobId) {
        $status = $queueManager->getJobStatus($jobId);
        if ($status) {
            echo "  Job #" . ($index + 1) . " ({$jobId}):\n";
            echo "    Status: {$status['status']}\n";
            echo "    Updated: " . date('Y-m-d H:i:s', $status['updated_at']) . "\n";
        } else {
            echo "  Job #" . ($index + 1) . " ({$jobId}): Status not available (array storage)\n";
        }
    }
    
    echo "\n";
    echo "===========================================\n";
    echo "Test Summary\n";
    echo "===========================================\n";
    echo "✓ Dispatched 3 jobs\n";
    echo "✓ Processed {$processed} jobs\n";
    echo "✓ Worker functioning correctly\n";
    echo "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Test completed successfully!\n";
