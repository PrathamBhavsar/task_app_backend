<?php

declare(strict_types=1);

namespace Framework\Queue;

class Worker
{
    private QueueManager $queue;
    private bool $shouldQuit = false;

    public function __construct(QueueManager $queue)
    {
        $this->queue = $queue;
    }

    public function work(string $queueName = 'default'): void
    {
        echo "Worker started for queue: {$queueName}\n";

        while (!$this->shouldQuit) {
            $payload = $this->queue->pop($queueName);

            if ($payload === null) {
                // No jobs available, sleep for a bit
                sleep(1);
                continue;
            }

            $this->process($payload);
        }

        echo "Worker stopped\n";
    }

    public function process(array $payload): void
    {
        $jobId = $payload['id'];
        $attempts = $payload['attempts'] ?? 0;

        try {
            echo "Processing job {$jobId} (attempt " . ($attempts + 1) . ")\n";

            $job = unserialize($payload['job']);
            
            if (!$job instanceof JobInterface) {
                throw new \RuntimeException('Invalid job type');
            }

            // Update status to processing
            $this->queue->updateJobStatus($jobId, 'processing');

            // Execute the job
            $job->handle();

            // Update status to completed
            $this->queue->updateJobStatus($jobId, 'completed');

            echo "Job {$jobId} completed successfully\n";
        } catch (\Throwable $e) {
            echo "Job {$jobId} failed: " . $e->getMessage() . "\n";

            $attempts++;
            $job = unserialize($payload['job']);

            if ($attempts < $job->getMaxTries()) {
                // Retry with exponential backoff
                $delay = $job->getRetryDelay() * pow(2, $attempts - 1);
                echo "Retrying job {$jobId} in {$delay} seconds (attempt {$attempts}/{$job->getMaxTries()})\n";

                // Re-queue the job with updated attempt count
                $payload['attempts'] = $attempts;
                $this->queue->laterWithPayload($payload, $delay);
                $this->queue->updateJobStatus($jobId, 'retrying', $e->getMessage());
            } else {
                // Max retries reached, mark as failed
                echo "Job {$jobId} failed permanently after {$attempts} attempts\n";
                $job->failed($e);
                $this->queue->updateJobStatus($jobId, 'failed', $e->getMessage());
            }
        }
    }

    public function stop(): void
    {
        $this->shouldQuit = true;
    }
}
