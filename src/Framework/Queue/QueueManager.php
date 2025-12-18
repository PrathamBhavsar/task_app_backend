<?php

declare(strict_types=1);

namespace Framework\Queue;

class QueueManager
{
    private array $storage = [];
    private ?object $redis = null;

    public function __construct(?object $redis = null)
    {
        $this->redis = $redis;
    }

    public function push(JobInterface $job, ?string $queue = null): string
    {
        $queue = $queue ?? 'default';
        $jobId = $this->generateJobId();
        
        $payload = [
            'id' => $jobId,
            'job' => serialize($job),
            'attempts' => 0,
            'created_at' => time(),
        ];

        if ($this->redis) {
            $this->redis->rpush("queue:{$queue}", json_encode($payload));
        } else {
            if (!isset($this->storage[$queue])) {
                $this->storage[$queue] = [];
            }
            $this->storage[$queue][] = $payload;
        }

        return $jobId;
    }

    public function later(JobInterface $job, int $delay, ?string $queue = null): string
    {
        $queue = $queue ?? 'default';
        $jobId = $this->generateJobId();
        $executeAt = time() + $delay;
        
        $payload = [
            'id' => $jobId,
            'job' => serialize($job),
            'attempts' => 0,
            'created_at' => time(),
            'execute_at' => $executeAt,
        ];

        if ($this->redis) {
            $this->redis->zadd("queue:{$queue}:delayed", $executeAt, json_encode($payload));
        } else {
            if (!isset($this->storage["{$queue}:delayed"])) {
                $this->storage["{$queue}:delayed"] = [];
            }
            $this->storage["{$queue}:delayed"][] = $payload;
        }

        return $jobId;
    }

    public function laterWithPayload(array $payload, int $delay, ?string $queue = null): void
    {
        $queue = $queue ?? 'default';
        $executeAt = time() + $delay;
        
        $payload['execute_at'] = $executeAt;

        if ($this->redis) {
            $this->redis->zadd("queue:{$queue}:delayed", $executeAt, json_encode($payload));
        } else {
            if (!isset($this->storage["{$queue}:delayed"])) {
                $this->storage["{$queue}:delayed"] = [];
            }
            $this->storage["{$queue}:delayed"][] = $payload;
        }
    }

    public function pop(string $queue = 'default'): ?array
    {
        // Move delayed jobs that are ready
        $this->moveDelayedJobs($queue);

        if ($this->redis) {
            $payload = $this->redis->lpop("queue:{$queue}");
            return $payload ? json_decode($payload, true) : null;
        }

        if (isset($this->storage[$queue]) && !empty($this->storage[$queue])) {
            return array_shift($this->storage[$queue]);
        }

        return null;
    }

    public function updateJobStatus(string $jobId, string $status, ?string $error = null): void
    {
        $key = "job:status:{$jobId}";
        $data = [
            'status' => $status,
            'updated_at' => time(),
        ];

        if ($error) {
            $data['error'] = $error;
        }

        if ($this->redis) {
            $this->redis->setex($key, 86400, json_encode($data)); // Keep for 24 hours
        }
    }

    public function getJobStatus(string $jobId): ?array
    {
        $key = "job:status:{$jobId}";

        if ($this->redis) {
            $data = $this->redis->get($key);
            return $data ? json_decode($data, true) : null;
        }

        return null;
    }

    private function moveDelayedJobs(string $queue): void
    {
        if ($this->redis) {
            $now = time();
            $jobs = $this->redis->zrangebyscore("queue:{$queue}:delayed", 0, $now);
            
            foreach ($jobs as $job) {
                $this->redis->rpush("queue:{$queue}", $job);
                $this->redis->zrem("queue:{$queue}:delayed", $job);
            }
        } else {
            $delayedKey = "{$queue}:delayed";
            if (isset($this->storage[$delayedKey])) {
                $now = time();
                $ready = [];
                $remaining = [];
                
                foreach ($this->storage[$delayedKey] as $payload) {
                    if ($payload['execute_at'] <= $now) {
                        $ready[] = $payload;
                    } else {
                        $remaining[] = $payload;
                    }
                }
                
                $this->storage[$delayedKey] = $remaining;
                
                if (!isset($this->storage[$queue])) {
                    $this->storage[$queue] = [];
                }
                $this->storage[$queue] = array_merge($this->storage[$queue], $ready);
            }
        }
    }

    private function generateJobId(): string
    {
        return uniqid('job_', true);
    }
}
