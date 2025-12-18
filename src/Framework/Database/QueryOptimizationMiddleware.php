<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\MiddlewareInterface;
use Framework\Middleware\RequestHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Middleware that tracks query optimization metrics and detects N+1 problems
 */
class QueryOptimizationMiddleware implements MiddlewareInterface
{
    private QueryLogger $queryLogger;
    private LoggerInterface $logger;
    private int $queryThreshold;
    private bool $detectNPlusOne;

    public function __construct(
        QueryLogger $queryLogger,
        ?LoggerInterface $logger = null,
        int $queryThreshold = 50,
        bool $detectNPlusOne = true
    ) {
        $this->queryLogger = $queryLogger;
        $this->logger = $logger ?? new NullLogger();
        $this->queryThreshold = $queryThreshold;
        $this->detectNPlusOne = $detectNPlusOne;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Reset query logger at the start of the request
        $this->queryLogger->reset();
        
        // Process the request
        $response = $handler->handle($request);
        
        // Get query statistics
        $stats = $this->queryLogger->getStatistics();
        
        // Log query statistics
        $this->logger->info('Request query statistics', [
            'method' => $request->method,
            'path' => $request->getPath(),
            'query_count' => $stats['total_queries'],
            'total_duration' => $stats['total_duration'] . 's',
            'average_duration' => $stats['average_duration'] . 's',
            'slow_queries' => $stats['slow_queries'],
        ]);
        
        // Check if query count exceeds threshold
        if ($stats['total_queries'] > $this->queryThreshold) {
            $this->logger->warning('Query count threshold exceeded', [
                'method' => $request->method,
                'path' => $request->getPath(),
                'query_count' => $stats['total_queries'],
                'threshold' => $this->queryThreshold,
                'message' => sprintf(
                    'Request executed %d queries (threshold: %d). Consider optimizing with eager loading.',
                    $stats['total_queries'],
                    $this->queryThreshold
                ),
            ]);
        }
        
        // Detect N+1 query problems
        if ($this->detectNPlusOne) {
            $nPlusOneIssues = $this->queryLogger->detectNPlusOneQueries();
            
            if (!empty($nPlusOneIssues)) {
                foreach ($nPlusOneIssues as $issue) {
                    $this->logger->warning('N+1 query problem detected', [
                        'method' => $request->method,
                        'path' => $request->getPath(),
                        'pattern' => $issue['pattern'],
                        'count' => $issue['count'],
                        'example_sql' => $issue['example_sql'],
                        'suggestion' => 'Consider using eager loading or batch fetching to optimize this query pattern.',
                    ]);
                }
            }
        }
        
        // Add query statistics to response headers in development mode
        if ($this->queryLogger->isEnabled()) {
            $response = $response->withHeader('X-Database-Query-Count', (string) $stats['total_queries']);
            $response = $response->withHeader('X-Database-Query-Time', round($stats['total_duration'] * 1000, 2) . 'ms');
            
            if ($stats['potential_n_plus_one'] > 0) {
                $response = $response->withHeader('X-Database-N-Plus-One-Detected', (string) $stats['potential_n_plus_one']);
            }
        }
        
        return $response;
    }
}
