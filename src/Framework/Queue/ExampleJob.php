<?php

declare(strict_types=1);

namespace Framework\Queue;

/**
 * Example Job Implementation
 * 
 * This is a sample job that demonstrates how to implement the JobInterface.
 * Use this as a template for creating your own jobs.
 */
class ExampleJob implements JobInterface
{
    private string $data;
    private int $processingTime;

    /**
     * @param string $data Data to process
     * @param int $processingTime Simulated processing time in seconds
     */
    public function __construct(string $data, int $processingTime = 2)
    {
        $this->data = $data;
        $this->processingTime = $processingTime;
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        echo "Processing job with data: {$this->data}\n";
        
        // Simulate some work
        sleep($this->processingTime);
        
        // Uncomment to simulate random failures for testing retry logic
        // if (rand(1, 3) === 1) {
        //     throw new \RuntimeException('Random failure for testing');
        // }
        
        echo "Job completed successfully!\n";
    }

    /**
     * Handle job failure after all retries exhausted
     */
    public function failed(\Throwable $exception): void
    {
        echo "Job failed permanently: {$exception->getMessage()}\n";
        
        // In a real application, you might:
        // - Log the failure to a monitoring service
        // - Send a notification to administrators
        // - Store failure details in a database
        // - Trigger a fallback process
        
        error_log(sprintf(
            "ExampleJob failed: %s\nData: %s\nException: %s",
            $exception->getMessage(),
            $this->data,
            $exception->getTraceAsString()
        ));
    }

    /**
     * Get maximum number of retry attempts
     */
    public function getMaxTries(): int
    {
        return 3;
    }

    /**
     * Get base retry delay in seconds
     * 
     * Actual delay uses exponential backoff:
     * - Attempt 1: immediate
     * - Attempt 2: 60 seconds
     * - Attempt 3: 120 seconds
     * - Attempt 4: 240 seconds
     */
    public function getRetryDelay(): int
    {
        return 60;
    }
}
