<?php

/**
 * Example usage of Database Query Optimization Tools
 * 
 * This file demonstrates how to use the query optimization tools
 * to detect and fix N+1 query problems.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Framework\Database\QueryLogger;
use Framework\Database\DoctrineQueryLogger;
use Framework\Database\QueryOptimizationMiddleware;
use Framework\Database\EagerLoadingHelper;
use Framework\Metrics\MetricsCollector;
use Psr\Log\NullLogger;

// ============================================================================
// Example 1: Basic Query Logging
// ============================================================================

echo "Example 1: Basic Query Logging\n";
echo str_repeat("=", 80) . "\n\n";

$logger = new NullLogger();
$queryLogger = new QueryLogger($logger, true, 10);

// Simulate some queries
$queryLogger->startQuery("SELECT * FROM users WHERE id = ?", [1]);
usleep(5000); // Simulate 5ms query
$queryLogger->stopQuery("SELECT * FROM users WHERE id = ?", [1]);

$queryLogger->startQuery("SELECT * FROM orders WHERE user_id = ?", [1]);
usleep(3000);
$queryLogger->stopQuery("SELECT * FROM orders WHERE user_id = ?", [1]);

// Get statistics
$stats = $queryLogger->getStatistics();
echo "Query Statistics:\n";
echo "  Total Queries: {$stats['total_queries']}\n";
echo "  Total Duration: {$stats['total_duration']}s\n";
echo "  Average Duration: {$stats['average_duration']}s\n";
echo "  Slow Queries: {$stats['slow_queries']}\n";
echo "\n";

// ============================================================================
// Example 2: N+1 Query Detection
// ============================================================================

echo "Example 2: N+1 Query Detection\n";
echo str_repeat("=", 80) . "\n\n";

$queryLogger->reset();

// Simulate N+1 query pattern (same query executed multiple times)
for ($i = 1; $i <= 15; $i++) {
    $queryLogger->startQuery("SELECT * FROM orders WHERE user_id = ?", [$i]);
    usleep(2000);
    $queryLogger->stopQuery("SELECT * FROM orders WHERE user_id = ?", [$i]);
}

// Detect N+1 problems
$issues = $queryLogger->detectNPlusOneQueries();
echo "N+1 Query Issues Detected: " . count($issues) . "\n";
foreach ($issues as $issue) {
    echo "  - {$issue['message']}\n";
    echo "    Pattern: {$issue['pattern']}\n";
    echo "    Count: {$issue['count']}\n";
}
echo "\n";

// ============================================================================
// Example 3: Eager Loading Helper - Basic Usage
// ============================================================================

echo "Example 3: Eager Loading Helper - Basic Usage\n";
echo str_repeat("=", 80) . "\n\n";

// This is a pseudo-code example showing how to use EagerLoadingHelper
// In a real application, you would use this with Doctrine QueryBuilder

echo "// BAD: N+1 query problem\n";
echo "\$users = \$repository->findAll();\n";
echo "foreach (\$users as \$user) {\n";
echo "    echo \$user->getProfile()->getBio(); // Triggers a query for each user\n";
echo "}\n\n";

echo "// GOOD: Use eager loading\n";
echo "\$qb = \$repository->createQueryBuilder('u');\n";
echo "EagerLoadingHelper::addEagerLoading(\$qb, 'u', ['profile']);\n";
echo "\$users = \$qb->getQuery()->getResult();\n";
echo "foreach (\$users as \$user) {\n";
echo "    echo \$user->getProfile()->getBio(); // No additional queries\n";
echo "}\n\n";

// ============================================================================
// Example 4: Optimization Suggestions
// ============================================================================

echo "Example 4: Optimization Suggestions\n";
echo str_repeat("=", 80) . "\n\n";

$queryLogger->reset();

// Simulate a request with many queries
for ($i = 1; $i <= 60; $i++) {
    $queryLogger->startQuery("SELECT * FROM products WHERE id = ?", [$i]);
    usleep(1000);
    $queryLogger->stopQuery("SELECT * FROM products WHERE id = ?", [$i]);
}

$stats = $queryLogger->getStatistics();
$suggestions = EagerLoadingHelper::getOptimizationSuggestions($stats);

echo "Optimization Suggestions:\n";
foreach ($suggestions as $suggestion) {
    echo "  [{$suggestion['severity']}] {$suggestion['message']}\n";
    echo "  Example: {$suggestion['example']}\n\n";
}

// ============================================================================
// Example 5: Comprehensive Query Optimization
// ============================================================================

echo "Example 5: Comprehensive Query Optimization\n";
echo str_repeat("=", 80) . "\n\n";

echo "// Optimize a query with multiple strategies\n";
echo "\$qb = EagerLoadingHelper::optimize(\n";
echo "    \$repository->createQueryBuilder('u'),\n";
echo "    'u',\n";
echo "    [\n";
echo "        'associations' => ['profile', 'orders', 'orders.items'],\n";
echo "        'partial' => ['id', 'name', 'email'],\n";
echo "        'criteria' => ['u.active' => true],\n";
echo "        'page' => 1,\n";
echo "        'perPage' => 20,\n";
echo "        'batchSize' => 100\n";
echo "    ]\n";
echo ");\n";
echo "\$users = \$qb->getQuery()->getResult();\n\n";

// ============================================================================
// Example 6: Integration with Metrics
// ============================================================================

echo "Example 6: Integration with Metrics\n";
echo str_repeat("=", 80) . "\n\n";

$metrics = new MetricsCollector();
$queryLogger = new QueryLogger($logger, true);
$doctrineLogger = new DoctrineQueryLogger($metrics, $queryLogger);

// Simulate queries
$doctrineLogger->startQuery("SELECT * FROM users", []);
usleep(5000);
$doctrineLogger->stopQuery();

$doctrineLogger->startQuery("SELECT * FROM orders", []);
usleep(3000);
$doctrineLogger->stopQuery();

// Get metrics
$metricsData = $metrics->getMetrics();
echo "Metrics Collected:\n";
echo "  Counters: " . count($metricsData['counters']) . "\n";
echo "  Histograms: " . count($metricsData['histograms']) . "\n";
echo "\n";

// Export to Prometheus format
echo "Prometheus Export:\n";
echo $metrics->exportPrometheus();
echo "\n";

// ============================================================================
// Example 7: Query Logger Statistics
// ============================================================================

echo "Example 7: Detailed Query Statistics\n";
echo str_repeat("=", 80) . "\n\n";

$queryLogger->reset();

// Simulate various query patterns
$queries = [
    ["SELECT * FROM users WHERE id = ?", [1], 5000],
    ["SELECT * FROM orders WHERE user_id = ?", [1], 3000],
    ["SELECT * FROM products WHERE category_id = ?", [5], 150000], // Slow query
    ["SELECT * FROM orders WHERE user_id = ?", [2], 3000],
    ["SELECT * FROM orders WHERE user_id = ?", [3], 3000],
];

foreach ($queries as [$sql, $params, $duration]) {
    $queryLogger->startQuery($sql, $params);
    usleep($duration);
    $queryLogger->stopQuery($sql, $params);
}

$stats = $queryLogger->getStatistics();
echo "Detailed Statistics:\n";
echo "  Total Queries: {$stats['total_queries']}\n";
echo "  Total Duration: " . round($stats['total_duration'] * 1000, 2) . "ms\n";
echo "  Average Duration: " . round($stats['average_duration'] * 1000, 2) . "ms\n";
echo "  Slow Queries (>100ms): {$stats['slow_queries']}\n";
echo "  Unique Query Patterns: {$stats['unique_patterns']}\n";
echo "  Potential N+1 Issues: {$stats['potential_n_plus_one']}\n";
echo "\n";

// Get all queries
$allQueries = $queryLogger->getQueries();
echo "Query Details:\n";
foreach ($allQueries as $index => $query) {
    $duration = round($query['duration'] * 1000, 2);
    echo "  Query " . ($index + 1) . ": {$duration}ms\n";
    echo "    SQL: {$query['sql']}\n";
    if (!empty($query['params'])) {
        echo "    Params: " . json_encode($query['params']) . "\n";
    }
}
echo "\n";

// ============================================================================
// Summary
// ============================================================================

echo "Summary\n";
echo str_repeat("=", 80) . "\n\n";
echo "The Database Query Optimization Tools provide:\n";
echo "  1. Comprehensive query logging in development mode\n";
echo "  2. Automatic N+1 query detection\n";
echo "  3. Query performance metrics and statistics\n";
echo "  4. Eager loading helpers to prevent N+1 problems\n";
echo "  5. Optimization suggestions based on query analysis\n";
echo "  6. Integration with Doctrine ORM and metrics collection\n";
echo "\n";
echo "Use these tools to identify and fix database performance issues early!\n";
