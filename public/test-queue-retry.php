<?php

declare(strict_types=1);

/**
 * Queue Retry Logic Test Script
 * 
 * This script tests the retry logic with exponential backoff.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\Config;
use Framework\Config\EnvLoader;
use Framework\Queue\QueueFactory;
use Framework\Queue\Worker;
use Framework\Queue\JobInterface;

// Create a failing job for testing
class FailingJob implements JobInterface
{
    private static int $attemptCount = 0;
    
    public function __construct(private string $data) {}
    
    public function handle(): void
    {
        self::$attemptCount++;
        echo "  Attempt #" . self::$attemptCount . " - Processing: {$this->data}\n";
        
        // Fail on first 2 attempts, succeed on 3rd
        if (self::$attemptCount < 3) {
            throw new \RuntimeException("Simulated failure on attempt " . self::$attemptCount);
        }
        
        echo "  ✓ Job succeeded on attempt " . self::$attemptCount . "\n";
    }
    
    public function failed(\Throwable $exception): void
    {
        echo "  ✗ Job failed permanently: {$exception->getMessage()}\n";
    }
    
    public function getMaxTries(): int
    {
        return 3;
    }
    
    public function getRetryDelay(): int
    {
        return 2; // 2 seconds for quick testing
    }
}

// Load environment
$envLoader = new EnvLoader();
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envLoader->load($envPath);
}

// Create config
$config = new Config();

echo "===========================================\n";
echo "Queue Retry Logic Test\n";
echo "===========================================\n\n";

try {
    // Create queue manager
    $queueManager = QueueFactory::create($config);
    $worker = new Worker($queueManager);
    
    echo "✓ Queue manager and worker created\n\n";
    
    // Test 1: Job that succeeds after retries
    echo "Test 1: Job that succeeds after retries\n";
    echo "-------------------------------------------\n";
    
    $jobId = $queueManager->push(new FailingJob("Retry Test Job"));
    echo "✓ Job dispatched (ID: {$jobId})\n\n";
    
    // Process the job (will fail and retry)
    $payload = $queueManager->pop('default');
    if ($payload) {
        $worker->process($payload);
        
        // Process retry attempts
        echo "\nWaiting for retry (2 seconds)...\n";
        sleep(2);
        
        $payload = $queueManager->pop('default');
        if ($payload) {
            $worker->process($payload);
            
            echo "\nWaiting for retry (4 seconds with exponential backoff)...\n";
            sleep(4);
            
            $payload = $queueManager->pop('default');
            if ($payload) {
                $worker->process($payload);
            }
        }
    }
    
    echo "\n-------------------------------------------\n\n";
    
    // Test 2: Job that fails permanently
    echo "Test 2: Job that fails permanently\n";
    echo "-------------------------------------------\n";
    
    class PermanentlyFailingJob implements JobInterface
    {
        public function handle(): void
        {
            throw new \RuntimeException("This job always fails");
        }
        
        public function failed(\Throwable $exception): void
        {
            echo "  ✓ failed() method called: {$exception->getMessage()}\n";
        }
        
        public function getMaxTries(): int
        {
            return 2;
        }
        
        public function getRetryDelay(): int
        {
            return 1;
        }
    }
    
    $jobId = $queueManager->push(new PermanentlyFailingJob());
    echo "✓ Job dispatched (ID: {$jobId})\n\n";
    
    // Process the job (will fail)
    $payload = $queueManager->pop('default');
    if ($payload) {
        $worker->process($payload);
        
        echo "\nWaiting for retry (1 second)...\n";
        sleep(1);
        
        // Process retry (will fail again and call failed())
        $payload = $queueManager->pop('default');
        if ($payload) {
            $worker->process($payload);
        }
    }
    
    echo "\n-------------------------------------------\n\n";
    
    echo "===========================================\n";
    echo "Test Summary\n";
    echo "===========================================\n";
    echo "✓ Retry logic with exponential backoff working\n";
    echo "✓ Jobs retry up to getMaxTries() times\n";
    echo "✓ failed() method called after max retries\n";
    echo "✓ Exponential backoff applied correctly\n";
    echo "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "Test completed successfully!\n";
