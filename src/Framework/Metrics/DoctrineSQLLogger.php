<?php

declare(strict_types=1);

namespace Framework\Metrics;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Doctrine SQL Logger that tracks database query metrics
 */
class DoctrineSQLLogger implements SQLLogger
{
    private MetricsCollector $metrics;
    private ?float $queryStart = null;
    private int $queryCount = 0;

    public function __construct(MetricsCollector $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->queryStart = microtime(true);
        $this->queryCount++;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery(): void
    {
        if ($this->queryStart === null) {
            return;
        }

        $duration = microtime(true) - $this->queryStart;
        
        // Record query duration
        $this->metrics->histogram('db_query_duration_seconds', $duration);
        
        // Increment total queries counter
        $this->metrics->increment('db_queries_total');
        
        $this->queryStart = null;
    }

    /**
     * Get the total number of queries executed
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * Reset the query count
     */
    public function resetQueryCount(): void
    {
        $this->queryCount = 0;
    }
}
