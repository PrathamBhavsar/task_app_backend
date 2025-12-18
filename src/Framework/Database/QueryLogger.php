<?php

declare(strict_types=1);

namespace Framework\Database;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Query logger that logs all database queries in development mode
 * and detects potential N+1 query problems
 */
class QueryLogger
{
    private LoggerInterface $logger;
    private bool $enabled;
    private array $queries = [];
    private array $queryPatterns = [];
    private int $duplicateThreshold;
    private ?float $currentQueryStart = null;

    public function __construct(
        ?LoggerInterface $logger = null,
        bool $enabled = false,
        int $duplicateThreshold = 10
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->enabled = $enabled;
        $this->duplicateThreshold = $duplicateThreshold;
    }

    /**
     * Log the start of a query
     */
    public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->currentQueryStart = microtime(true);
        
        // Normalize the query to detect patterns (replace parameter placeholders)
        $normalizedSql = $this->normalizeQuery($sql);
        
        // Track query patterns for N+1 detection
        if (!isset($this->queryPatterns[$normalizedSql])) {
            $this->queryPatterns[$normalizedSql] = [
                'count' => 0,
                'example_sql' => $sql,
                'example_params' => $params,
            ];
        }
        $this->queryPatterns[$normalizedSql]['count']++;
    }

    /**
     * Log the completion of a query
     */
    public function stopQuery(string $sql, ?array $params = null): void
    {
        if (!$this->enabled || $this->currentQueryStart === null) {
            return;
        }

        $duration = microtime(true) - $this->currentQueryStart;
        
        $queryLog = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'timestamp' => microtime(true),
        ];
        
        $this->queries[] = $queryLog;
        
        // Log slow queries (> 100ms)
        if ($duration > 0.1) {
            $this->logger->warning('Slow query detected', [
                'sql' => $sql,
                'params' => $params,
                'duration' => round($duration * 1000, 2) . 'ms',
            ]);
        }
        
        // Log the query in debug mode
        $this->logger->debug('Database query executed', [
            'sql' => $sql,
            'params' => $params,
            'duration' => round($duration * 1000, 2) . 'ms',
        ]);
        
        $this->currentQueryStart = null;
    }

    /**
     * Get all logged queries
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Get the total number of queries executed
     */
    public function getQueryCount(): int
    {
        return count($this->queries);
    }

    /**
     * Detect potential N+1 query problems
     * Returns an array of detected issues
     */
    public function detectNPlusOneQueries(): array
    {
        $issues = [];
        
        foreach ($this->queryPatterns as $pattern => $data) {
            if ($data['count'] >= $this->duplicateThreshold) {
                $issues[] = [
                    'type' => 'n_plus_one',
                    'pattern' => $pattern,
                    'count' => $data['count'],
                    'example_sql' => $data['example_sql'],
                    'example_params' => $data['example_params'],
                    'message' => sprintf(
                        'Potential N+1 query detected: Same query pattern executed %d times',
                        $data['count']
                    ),
                ];
                
                $this->logger->warning('Potential N+1 query detected', [
                    'pattern' => $pattern,
                    'count' => $data['count'],
                    'example_sql' => $data['example_sql'],
                ]);
            }
        }
        
        return $issues;
    }

    /**
     * Get query statistics
     */
    public function getStatistics(): array
    {
        $totalDuration = 0;
        $slowQueries = 0;
        
        foreach ($this->queries as $query) {
            $totalDuration += $query['duration'];
            if ($query['duration'] > 0.1) {
                $slowQueries++;
            }
        }
        
        return [
            'total_queries' => count($this->queries),
            'total_duration' => round($totalDuration, 4),
            'average_duration' => count($this->queries) > 0 
                ? round($totalDuration / count($this->queries), 4) 
                : 0,
            'slow_queries' => $slowQueries,
            'unique_patterns' => count($this->queryPatterns),
            'potential_n_plus_one' => count($this->detectNPlusOneQueries()),
        ];
    }

    /**
     * Reset all logged queries and patterns
     */
    public function reset(): void
    {
        $this->queries = [];
        $this->queryPatterns = [];
        $this->currentQueryStart = null;
    }

    /**
     * Normalize a SQL query to detect patterns
     * Replaces specific values with placeholders
     */
    private function normalizeQuery(string $sql): string
    {
        // Remove extra whitespace
        $normalized = preg_replace('/\s+/', ' ', trim($sql));
        
        // Replace numeric literals
        $normalized = preg_replace('/\b\d+\b/', '?', $normalized);
        
        // Replace string literals
        $normalized = preg_replace("/'[^']*'/", '?', $normalized);
        
        // Replace parameter placeholders (?, :param, etc.)
        $normalized = preg_replace('/:\w+/', '?', $normalized);
        
        return $normalized;
    }

    /**
     * Enable query logging
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable query logging
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if query logging is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
