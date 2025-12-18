# Database Query Optimization Tools

This directory contains tools for optimizing database queries, detecting N+1 query problems, and improving overall database performance.

## Components

### QueryLogger

The `QueryLogger` class logs all database queries in development mode and detects potential N+1 query problems.

**Features:**
- Logs all SQL queries with parameters and execution time
- Detects slow queries (>100ms)
- Identifies N+1 query patterns
- Provides query statistics and analysis
- Normalizes queries to detect duplicate patterns

**Usage:**

```php
use Framework\Database\QueryLogger;
use Psr\Log\LoggerInterface;

// Create query logger (enabled in development mode)
$queryLogger = new QueryLogger(
    logger: $logger,
    enabled: $_ENV['APP_ENV'] === 'development',
    duplicateThreshold: 10
);

// Start logging a query
$queryLogger->startQuery($sql, $params);

// Stop logging a query
$queryLogger->stopQuery($sql, $params);

// Get all queries
$queries = $queryLogger->getQueries();

// Get query statistics
$stats = $queryLogger->getStatistics();
// Returns: [
//     'total_queries' => 45,
//     'total_duration' => 0.523,
//     'average_duration' => 0.0116,
//     'slow_queries' => 2,
//     'unique_patterns' => 12,
//     'potential_n_plus_one' => 1
// ]

// Detect N+1 query problems
$issues = $queryLogger->detectNPlusOneQueries();
```

### DoctrineQueryLogger

The `DoctrineQueryLogger` integrates with Doctrine ORM and combines query logging with metrics collection.

**Usage:**

```php
use Framework\Database\DoctrineQueryLogger;
use Framework\Database\QueryLogger;
use Framework\Metrics\MetricsCollector;
use Doctrine\ORM\EntityManager;

// Create loggers
$metrics = new MetricsCollector();
$queryLogger = new QueryLogger($logger, true);
$doctrineLogger = new DoctrineQueryLogger($metrics, $queryLogger);

// Configure Doctrine to use the logger
$entityManager->getConnection()
    ->getConfiguration()
    ->setSQLLogger($doctrineLogger);
```

### QueryOptimizationMiddleware

Middleware that tracks query optimization metrics per request and detects N+1 problems.

**Features:**
- Tracks query count per request
- Logs warnings when query threshold is exceeded
- Detects N+1 query patterns
- Adds query statistics to response headers in development mode

**Usage:**

```php
use Framework\Database\QueryOptimizationMiddleware;

// Add to middleware pipeline
$pipeline->pipe(new QueryOptimizationMiddleware(
    queryLogger: $queryLogger,
    logger: $logger,
    queryThreshold: 50,
    detectNPlusOne: true
));
```

**Response Headers (Development Mode):**
```
X-Database-Query-Count: 23
X-Database-Query-Time: 145.32ms
X-Database-N-Plus-One-Detected: 1
```

### EagerLoadingHelper

Helper class for configuring Doctrine eager loading to prevent N+1 queries.

**Features:**
- Add eager loading (JOIN FETCH) to queries
- Configure batch fetching
- Partial object loading for list views
- Pagination with eager loading
- Optimization suggestions based on query analysis

**Usage:**

#### Basic Eager Loading

```php
use Framework\Database\EagerLoadingHelper;

// Add eager loading to a query
$qb = $repository->createQueryBuilder('u');
EagerLoadingHelper::addEagerLoading($qb, 'u', [
    'profile',
    'orders',
    'orders.items'
]);

$users = $qb->getQuery()->getResult();
```

#### Create Optimized Query

```php
// Create query with eager loading and criteria
$qb = EagerLoadingHelper::createEagerQuery(
    $repository->createQueryBuilder('u'),
    'u',
    ['profile', 'orders'],
    ['u.active' => true]
);
```

#### Partial Object Loading

```php
// Load only specific fields for list views
$qb = $repository->createQueryBuilder('u');
EagerLoadingHelper::selectPartial($qb, 'u', ['id', 'name', 'email']);
```

#### Pagination with Eager Loading

```php
$qb = $repository->createQueryBuilder('u');
EagerLoadingHelper::addEagerLoading($qb, 'u', ['profile']);
EagerLoadingHelper::addPagination($qb, $page, $perPage);
```

#### Comprehensive Optimization

```php
// Apply multiple optimizations at once
$qb = EagerLoadingHelper::optimize(
    $repository->createQueryBuilder('u'),
    'u',
    [
        'associations' => ['profile', 'orders'],
        'partial' => ['id', 'name', 'email'],
        'criteria' => ['u.active' => true],
        'page' => 1,
        'perPage' => 20,
        'batchSize' => 100
    ]
);
```

#### Get Optimization Suggestions

```php
// Get suggestions based on query statistics
$stats = $queryLogger->getStatistics();
$suggestions = EagerLoadingHelper::getOptimizationSuggestions($stats);

foreach ($suggestions as $suggestion) {
    echo $suggestion['severity'] . ': ' . $suggestion['message'] . "\n";
    echo 'Example: ' . $suggestion['example'] . "\n";
}
```

## Configuration

Add these environment variables to your `.env` file:

```env
# Enable query logging in development
APP_ENV=development
APP_DEBUG=true

# Query optimization settings
DB_QUERY_THRESHOLD=50
DB_QUERY_LOG_SLOW=true
DB_QUERY_SLOW_THRESHOLD=100
DB_DETECT_N_PLUS_ONE=true
DB_N_PLUS_ONE_THRESHOLD=10
```

## Integration with Doctrine

### Setup in Bootstrap

```php
use Framework\Database\QueryLogger;
use Framework\Database\DoctrineQueryLogger;
use Framework\Database\QueryOptimizationMiddleware;
use Framework\Metrics\MetricsCollector;

// Create components
$config = new Config();
$metrics = new MetricsCollector();
$logger = /* your PSR-3 logger */;

// Create query logger
$queryLogger = new QueryLogger(
    logger: $logger,
    enabled: $config->getString('APP_ENV') === 'development',
    duplicateThreshold: $config->getInt('DB_N_PLUS_ONE_THRESHOLD', 10)
);

// Create Doctrine logger
$doctrineLogger = new DoctrineQueryLogger($metrics, $queryLogger);

// Configure Doctrine EntityManager
$entityManager->getConnection()
    ->getConfiguration()
    ->setSQLLogger($doctrineLogger);

// Add middleware to pipeline
$pipeline->pipe(new QueryOptimizationMiddleware(
    queryLogger: $queryLogger,
    logger: $logger,
    queryThreshold: $config->getInt('DB_QUERY_THRESHOLD', 50),
    detectNPlusOne: $config->getBool('DB_DETECT_N_PLUS_ONE', true)
));
```

## Common N+1 Query Patterns

### Problem: Loading Related Entities in a Loop

```php
// BAD: N+1 query problem
$users = $repository->findAll();
foreach ($users as $user) {
    echo $user->getProfile()->getBio(); // Triggers a query for each user
}
```

### Solution: Use Eager Loading

```php
// GOOD: Single query with JOIN
$qb = $repository->createQueryBuilder('u');
EagerLoadingHelper::addEagerLoading($qb, 'u', ['profile']);
$users = $qb->getQuery()->getResult();

foreach ($users as $user) {
    echo $user->getProfile()->getBio(); // No additional queries
}
```

### Problem: Nested Relationships

```php
// BAD: N+1 for orders and items
$users = $repository->findAll();
foreach ($users as $user) {
    foreach ($user->getOrders() as $order) {
        foreach ($order->getItems() as $item) {
            echo $item->getName();
        }
    }
}
```

### Solution: Eager Load Nested Relationships

```php
// GOOD: Single query with nested JOINs
$qb = $repository->createQueryBuilder('u');
EagerLoadingHelper::addEagerLoading($qb, 'u', [
    'orders',
    'orders.items'
]);
$users = $qb->getQuery()->getResult();
```

## Monitoring and Alerts

### Query Statistics Logging

The middleware automatically logs query statistics for each request:

```
[INFO] Request query statistics
  method: GET
  path: /api/users
  query_count: 23
  total_duration: 0.145s
  average_duration: 0.0063s
  slow_queries: 1
```

### Threshold Warnings

When query count exceeds the threshold:

```
[WARNING] Query count threshold exceeded
  method: GET
  path: /api/users
  query_count: 67
  threshold: 50
  message: Request executed 67 queries (threshold: 50). Consider optimizing with eager loading.
```

### N+1 Detection Warnings

When N+1 patterns are detected:

```
[WARNING] N+1 query problem detected
  method: GET
  path: /api/users
  pattern: SELECT * FROM orders WHERE user_id = ?
  count: 45
  example_sql: SELECT * FROM orders WHERE user_id = 123
  suggestion: Consider using eager loading or batch fetching to optimize this query pattern.
```

## Best Practices

1. **Enable Query Logging in Development**: Always enable query logging in development to catch N+1 problems early.

2. **Set Appropriate Thresholds**: Configure query count thresholds based on your application's needs.

3. **Use Eager Loading for Collections**: When loading entities with relationships, always use eager loading.

4. **Partial Loading for Lists**: Use partial object loading for list views to reduce memory usage.

5. **Monitor Production Metrics**: Track query count and duration metrics in production to identify performance issues.

6. **Review Query Logs Regularly**: Regularly review query logs to identify optimization opportunities.

7. **Add Database Indexes**: Use query logs to identify frequently queried columns and add appropriate indexes.

8. **Batch Operations**: For bulk operations, use batch processing to reduce query count.

## Performance Tips

- **Eager Loading**: Use `JOIN FETCH` for one-to-one and many-to-one relationships
- **Batch Fetching**: Use batch fetching for one-to-many and many-to-many relationships
- **Partial Objects**: Load only needed fields for list views
- **Pagination**: Always paginate large result sets
- **Query Cache**: Enable Doctrine's second-level cache for frequently accessed data
- **Database Indexes**: Add indexes to frequently queried columns
- **Connection Pooling**: Use connection pooling in production

## Troubleshooting

### High Query Count

If you see high query counts:
1. Check for N+1 query patterns in the logs
2. Add eager loading for related entities
3. Consider using batch fetching for collections
4. Review your entity relationships and lazy loading configuration

### Slow Queries

If you see slow queries:
1. Use EXPLAIN to analyze query execution plans
2. Add database indexes to frequently queried columns
3. Optimize complex queries with query builder
4. Consider denormalization for read-heavy operations

### Memory Issues

If you encounter memory issues:
1. Use partial object loading for large result sets
2. Implement pagination
3. Use iterators for batch processing
4. Clear the entity manager periodically in long-running processes

## Examples

See the `examples/` directory for complete working examples of:
- Basic eager loading
- N+1 query detection
- Query optimization strategies
- Integration with repositories
- Custom optimization patterns
