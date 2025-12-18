<?php

declare(strict_types=1);

namespace Framework\Health;

class HealthChecker
{
    private array $checks = [];

    public function addCheck(string $name, HealthCheckInterface $check): void
    {
        $this->checks[$name] = $check;
    }

    public function check(): array
    {
        $results = [];
        $allHealthy = true;

        foreach ($this->checks as $name => $check) {
            $start = microtime(true);
            
            try {
                $result = $check->check();
                $duration = (microtime(true) - $start) * 1000; // Convert to ms
                
                $results[$name] = array_merge(
                    $result->toArray(),
                    ['response_time_ms' => round($duration, 2)]
                );
                
                if (!$result->healthy) {
                    $allHealthy = false;
                }
            } catch (\Throwable $e) {
                $results[$name] = [
                    'status' => 'unhealthy',
                    'message' => $e->getMessage(),
                    'metadata' => [],
                ];
                $allHealthy = false;
            }
        }

        return [
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'checks' => $results,
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
        ];
    }
}
