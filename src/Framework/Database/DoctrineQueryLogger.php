<?php

declare(strict_types=1);

namespace Framework\Database;

use Doctrine\DBAL\Logging\SQLLogger;
use Framework\Metrics\MetricsCollector;

/**
 * Doctrine SQL Logger that integrates with QueryLogger and MetricsCollector
 * Provides comprehensive query tracking, metrics, and N+1 detection
 */
class DoctrineQueryLogger implements SQLLogger
{
    private MetricsCollector $metrics;
    private QueryLogger $queryLogger;
    private ?float $queryStart = null;
    private ?string $currentSql = null;
    private ?array $currentParams = null;

    public function __construct(
        MetricsCollector $metrics,
        QueryLogger $queryLogger
    ) {
        $this->metrics = $metrics;
        $this->queryLogger = $queryLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null): void
    {
        $this->queryStart = microtime(true);
        $this->currentSql = $sql;
        $this->currentParams = $params;
        
        // Log to QueryLogger for N+1 detection and detailed logging
        $this->queryLogger->startQuery($sql, $params, $types);
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
        
        // Record metrics
        $this->metrics->histogram('db_query_duration_seconds', $duration);
        $this->metrics->increment('db_queries_total');
        
        // Log to QueryLogger
        $this->queryLogger->stopQuery($this->currentSql ?? '', $this->currentParams);
        
        // Reset state
        $this->queryStart = null;
        $this->currentSql = null;
        $this->currentParams = null;
    }

    /**
     * Get the query logger instance
     */
    public function getQueryLogger(): QueryLogger
    {
        return $this->queryLogger;
    }
}
