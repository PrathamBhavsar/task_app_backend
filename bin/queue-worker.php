#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Queue Worker CLI Command
 * 
 * Usage:
 *   php bin/queue-worker.php [queue-name]
 * 
 * Examples:
 *   php bin/queue-worker.php              # Process default queue
 *   php bin/queue-worker.php emails       # Process emails queue
 *   php bin/queue-worker.php notifications # Process notifications queue
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\Config;
use Framework\Config\EnvLoader;
use Framework\Queue\QueueFactory;
use Framework\Queue\Worker;

// Load environment variables
$envLoader = new EnvLoader();
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envLoader->load($envPath);
}

// Load configuration
$config = new Config();

// Get queue name from command line argument
$queueName = $argv[1] ?? 'default';

echo "===========================================\n";
echo "Queue Worker Starting\n";
echo "===========================================\n";
echo "Queue: {$queueName}\n";
echo "Driver: " . $config->getString('queue.driver', 'redis') . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    // Create queue manager
    $queueManager = QueueFactory::create($config);
    
    // Create worker
    $worker = new Worker($queueManager);
    
    // Handle graceful shutdown
    $shutdown = function () use ($worker) {
        echo "\n\nReceived shutdown signal. Stopping worker gracefully...\n";
        $worker->stop();
    };
    
    // Register signal handlers for graceful shutdown
    if (function_exists('pcntl_signal')) {
        pcntl_signal(SIGTERM, $shutdown);
        pcntl_signal(SIGINT, $shutdown);
        pcntl_async_signals(true);
    }
    
    // Start processing jobs
    $worker->work($queueName);
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nWorker stopped successfully.\n";
exit(0);
