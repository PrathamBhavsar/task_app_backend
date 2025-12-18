# Queue System

The Queue system provides asynchronous job processing capabilities for long-running tasks that should not block HTTP requests.

## Features

- **Redis-backed storage**: Reliable job persistence using Redis
- **Delayed jobs**: Schedule jobs to run at a specific time
- **Retry logic**: Automatic retry with exponential backoff
- **Job status tracking**: Monitor job progress and failures
- **Multiple queues**: Organize jobs by priority or type
- **Graceful shutdown**: Workers handle shutdown signals properly

## Configuration

Configure the queue system in `config/queue.php`:

```php
return [
    'driver' => 'redis',  // 'redis' or 'array'
    'default' => 'default',
    
    'redis' => [
        'host' => 'localhost',
        'port' => 6379,
        'password' => null,
        'database' => 1,
    ],
];
```

Environment variables:
```env
QUEUE_DRIVER=redis
QUEUE_DEFAULT=default
REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_PASSWORD=
```

## Creating Jobs

Implement the `JobInterface` to create a job:

```php
<?php

use Framework\Queue\JobInterface;

class SendEmailJob implements JobInterface
{
    public function __construct(
        private string $to,
        private string $subject,
        private string $body
    ) {}
    
    public function handle(): void
    {
        // Send email logic
        mail($this->to, $this->subject, $this->body);
        
        echo "Email sent to {$this->to}\n";
    }
    
    public function failed(\Throwable $exception): void
    {
        // Handle failure (log, notify, etc.)
        error_log("Failed to send email to {$this->to}: " . $exception->getMessage());
    }
    
    public function getMaxTries(): int
    {
        return 3; // Retry up to 3 times
    }
    
    public function getRetryDelay(): int
    {
        return 60; // Wait 60 seconds before first retry (exponential backoff applies)
    }
}
```

## Dispatching Jobs

### Immediate Dispatch

```php
use Framework\Queue\QueueManager;

$queueManager = $container->resolve(QueueManager::class);

// Dispatch to default queue
$jobId = $queueManager->push(new SendEmailJob(
    to: 'user@example.com',
    subject: 'Welcome!',
    body: 'Thanks for signing up.'
));

// Dispatch to specific queue
$jobId = $queueManager->push(
    new SendEmailJob(...),
    queue: 'emails'
);
```

### Delayed Dispatch

```php
// Dispatch job to run in 5 minutes (300 seconds)
$jobId = $queueManager->later(
    new SendEmailJob(...),
    delay: 300
);

// Dispatch to specific queue with delay
$jobId = $queueManager->later(
    new SendEmailJob(...),
    delay: 300,
    queue: 'emails'
);
```

## Running Workers

### Command Line

Start a worker to process jobs:

```bash
# Process default queue
php bin/queue-worker.php

# Process specific queue
php bin/queue-worker.php emails

# Process notifications queue
php bin/queue-worker.php notifications
```

### Graceful Shutdown

Workers handle `SIGTERM` and `SIGINT` signals for graceful shutdown:

```bash
# Stop worker gracefully (Ctrl+C)
^C

# Or send SIGTERM
kill -TERM <worker-pid>
```

### Production Deployment

Use a process manager like Supervisor to keep workers running:

```ini
[program:queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/bin/queue-worker.php default
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/log/queue-worker.log
```

## Job Status Tracking

Track job status:

```php
$jobId = $queueManager->push(new SendEmailJob(...));

// Check status later
$status = $queueManager->getJobStatus($jobId);

if ($status) {
    echo "Status: " . $status['status'] . "\n";
    echo "Updated: " . date('Y-m-d H:i:s', $status['updated_at']) . "\n";
    
    if (isset($status['error'])) {
        echo "Error: " . $status['error'] . "\n";
    }
}
```

Job statuses:
- `processing`: Job is currently being executed
- `completed`: Job completed successfully
- `retrying`: Job failed but will be retried
- `failed`: Job failed permanently after max retries

## Retry Logic

Jobs are automatically retried with exponential backoff:

- **Attempt 1**: Immediate
- **Attempt 2**: After `getRetryDelay()` seconds (e.g., 60s)
- **Attempt 3**: After `getRetryDelay() * 2` seconds (e.g., 120s)
- **Attempt 4**: After `getRetryDelay() * 4` seconds (e.g., 240s)

The formula is: `delay * 2^(attempt - 1)`

## Multiple Queues

Organize jobs by priority or type:

```php
// High priority queue
$queueManager->push(new CriticalJob(), 'high-priority');

// Email queue
$queueManager->push(new SendEmailJob(...), 'emails');

// Notifications queue
$queueManager->push(new SendNotificationJob(...), 'notifications');

// Background processing queue
$queueManager->push(new ProcessDataJob(...), 'background');
```

Run separate workers for each queue:

```bash
php bin/queue-worker.php high-priority
php bin/queue-worker.php emails
php bin/queue-worker.php notifications
php bin/queue-worker.php background
```

## Example: Complete Workflow

```php
<?php

// 1. Create a job class
class ProcessOrderJob implements JobInterface
{
    public function __construct(private int $orderId) {}
    
    public function handle(): void
    {
        // Process order logic
        $order = Order::find($this->orderId);
        $order->process();
        
        // Send confirmation email
        $queueManager = app(QueueManager::class);
        $queueManager->push(new SendEmailJob(
            to: $order->customer->email,
            subject: 'Order Confirmed',
            body: "Your order #{$this->orderId} has been processed."
        ), 'emails');
    }
    
    public function failed(\Throwable $exception): void
    {
        error_log("Failed to process order {$this->orderId}: " . $exception->getMessage());
        
        // Notify admin
        $queueManager = app(QueueManager::class);
        $queueManager->push(new NotifyAdminJob(
            message: "Order processing failed: {$this->orderId}"
        ), 'notifications');
    }
    
    public function getMaxTries(): int
    {
        return 5;
    }
    
    public function getRetryDelay(): int
    {
        return 30;
    }
}

// 2. Dispatch the job from a controller
class OrderController
{
    public function store(CreateOrderRequest $request): Response
    {
        $order = Order::create($request->validated());
        
        // Dispatch job to process order asynchronously
        $jobId = $this->queueManager->push(
            new ProcessOrderJob($order->id),
            'orders'
        );
        
        return ApiResponse::success([
            'order_id' => $order->id,
            'job_id' => $jobId,
            'message' => 'Order created and queued for processing'
        ], 201);
    }
}

// 3. Run worker
// php bin/queue-worker.php orders
```

## Testing

### Unit Testing Jobs

```php
class SendEmailJobTest extends TestCase
{
    public function testJobHandlesSuccessfully(): void
    {
        $job = new SendEmailJob('test@example.com', 'Test', 'Body');
        
        $job->handle();
        
        // Assert email was sent
        $this->assertTrue(true);
    }
    
    public function testJobRetriesOnFailure(): void
    {
        $job = new SendEmailJob('invalid', 'Test', 'Body');
        
        $this->assertEquals(3, $job->getMaxTries());
        $this->assertEquals(60, $job->getRetryDelay());
    }
}
```

### Integration Testing

```php
class QueueIntegrationTest extends TestCase
{
    public function testJobIsProcessedByWorker(): void
    {
        $queueManager = new QueueManager(null); // Use array storage
        $worker = new Worker($queueManager);
        
        $jobId = $queueManager->push(new TestJob());
        
        $payload = $queueManager->pop();
        $worker->process($payload);
        
        $status = $queueManager->getJobStatus($jobId);
        $this->assertEquals('completed', $status['status']);
    }
}
```

## Best Practices

1. **Keep jobs small**: Each job should do one thing well
2. **Make jobs idempotent**: Jobs should be safe to run multiple times
3. **Handle failures gracefully**: Implement the `failed()` method
4. **Use appropriate retry settings**: Balance between reliability and resource usage
5. **Monitor job queues**: Track queue depth and processing times
6. **Use multiple queues**: Separate critical jobs from background tasks
7. **Log important events**: Help with debugging and monitoring
8. **Test jobs thoroughly**: Ensure jobs handle edge cases

## Troubleshooting

### Jobs not processing

1. Check if worker is running: `ps aux | grep queue-worker`
2. Check Redis connection: `redis-cli ping`
3. Check queue has jobs: `redis-cli llen queue:default`
4. Check worker logs for errors

### Jobs failing repeatedly

1. Check job logs for error messages
2. Verify job dependencies are available
3. Check `getMaxTries()` and `getRetryDelay()` settings
4. Implement better error handling in `handle()` method

### High memory usage

1. Restart workers periodically
2. Reduce number of concurrent workers
3. Optimize job memory usage
4. Use job batching for bulk operations

## Architecture

```
┌─────────────┐
│  Controller │
└──────┬──────┘
       │ push()
       ▼
┌─────────────┐
│QueueManager │
└──────┬──────┘
       │ serialize & store
       ▼
┌─────────────┐
│    Redis    │
└──────┬──────┘
       │ pop()
       ▼
┌─────────────┐
│   Worker    │
└──────┬──────┘
       │ process()
       ▼
┌─────────────┐
│     Job     │
└─────────────┘
```

## Related Components

- **Config**: Queue configuration management
- **Container**: Dependency injection for jobs
- **Metrics**: Track job processing metrics
- **Error Handler**: Log job failures
