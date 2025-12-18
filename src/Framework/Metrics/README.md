# Metrics Collection System

The Metrics Collection System provides comprehensive monitoring and observability for the API application. It collects performance metrics, tracks database queries, monitors cache efficiency, and supports custom business metrics.

## Features

- **Request Metrics**: Track HTTP request count, duration, and status codes
- **Database Metrics**: Monitor query count, duration, and detect N+1 query problems
- **Cache Metrics**: Track cache hit/miss ratios
- **Custom Business Metrics**: Register and track application-specific metrics
- **Prometheus Export**: Export metrics in Prometheus format for monitoring systems

## Components

### MetricsCollector

The core component that collects and stores metrics.

```php
use Framework\Metrics\MetricsCollector;

$metrics = new MetricsCollector();

// Increment a counter
$metrics->increment('user_registrations_total', ['source' => 'web']);

// Set a gauge value
$metrics->gauge('active_connections', 42.0);

// Record a histogram value
$metrics->histogram('request_size_bytes', 1024.5, ['endpoint' => '/api/users']);

// Record a timing
$metrics->timing('operation_duration_seconds', 0.125, ['operation' => 'db_query']);
```

### MetricsMiddleware

Automatically tracks HTTP request metrics.

```php
use Framework\Metrics\MetricsMiddleware;

// Tracks:
// - http_requests_total (counter)
// - http_request_duration_seconds (histogram)
// - http_requests_in_flight (gauge)
// - http_errors_total (counter)
```

### DoctrineSQLLogger

Tracks database query metrics using Doctrine's SQL logging.

```php
use Framework\Metrics\DoctrineSQLLogger;

$sqlLogger = new DoctrineSQLLogger($metrics);

// Configure Doctrine to use the logger
$config = new Configuration();
$config->setSQLLogger($sqlLogger);

// Tracks:
// - db_queries_total (counter)
// - db_query_duration_seconds (histogram)
```

### DatabaseMetricsMiddleware

Tracks database queries per request and warns about excessive queries.

```php
use Framework\Metrics\DatabaseMetricsMiddleware;

$middleware = new DatabaseMetricsMiddleware(
    $metrics,
    $sqlLogger,
    $queryThreshold = 50 // Warn if more than 50 queries per request
);

// Tracks:
// - db_queries_per_request (histogram)
// - db_query_threshold_exceeded (counter)
```

### Cache Metrics

The `CacheManager` automatically tracks cache hits and misses when a `MetricsCollector` is provided.

```php
use Framework\Cache\CacheManager;

$cacheManager = new CacheManager($store, $metrics);

// Automatically tracks:
// - cache_hits_total (counter)
// - cache_misses_total (counter)
```

## Custom Business Metrics

Register and track application-specific metrics:

```php
// Register a custom metric
$metrics->registerCustomMetric(
    name: 'orders_processed',
    type: 'counter',
    description: 'Total number of orders processed',
    labels: ['status' => 'pending']
);

// Record the metric
$metrics->recordCustomMetric('orders_processed', 1, ['status' => 'completed']);

// Or use the standard methods directly
$metrics->increment('orders_processed', ['status' => 'completed']);
```

## Prometheus Export

Export metrics in Prometheus format:

```php
$prometheusOutput = $metrics->exportPrometheus();

// Output example:
// # TYPE http_requests_total counter
// http_requests_total{method="GET",path="/api/users",status="200"} 42
// # TYPE http_request_duration_seconds histogram
// http_request_duration_seconds_count{method="GET",path="/api/users"} 42
// http_request_duration_seconds_sum{method="GET",path="/api/users"} 5.25
```

## Metrics Endpoints

### Prometheus Format

```
GET /metrics
Content-Type: text/plain; version=0.0.4; charset=utf-8
```

Returns metrics in Prometheus exposition format.

### JSON Format

```
GET /metrics/json
Content-Type: application/json
```

Returns metrics in JSON format with detailed statistics:

```json
{
  "counters": [
    {
      "name": "http_requests_total",
      "labels": {"method": "GET", "path": "/api/users", "status": "200"},
      "value": 42
    }
  ],
  "gauges": [
    {
      "name": "http_requests_in_flight",
      "labels": {},
      "value": 3
    }
  ],
  "histograms": [
    {
      "name": "http_request_duration_seconds",
      "labels": {"method": "GET", "path": "/api/users"},
      "count": 42,
      "sum": 5.25,
      "min": 0.05,
      "max": 0.5,
      "avg": 0.125,
      "p50": 0.12,
      "p95": 0.35,
      "p99": 0.48
    }
  ]
}
```

### Custom Metrics

```
GET /metrics/custom
Content-Type: application/json
```

Returns registered custom business metrics.

## Configuration

Enable or disable metrics collection in `config/monitoring.php`:

```php
return [
    'metrics' => [
        'enabled' => filter_var($_ENV['METRICS_ENABLED'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],
];
```

Environment variable:

```
METRICS_ENABLED=true
```

## Collected Metrics

### HTTP Metrics

| Metric | Type | Description | Labels |
|--------|------|-------------|--------|
| `http_requests_total` | Counter | Total HTTP requests | method, path, status |
| `http_request_duration_seconds` | Histogram | Request duration | method, path |
| `http_requests_in_flight` | Gauge | Current active requests | - |
| `http_errors_total` | Counter | Total HTTP errors | method, path, exception |

### Database Metrics

| Metric | Type | Description | Labels |
|--------|------|-------------|--------|
| `db_queries_total` | Counter | Total database queries | - |
| `db_query_duration_seconds` | Histogram | Query duration | - |
| `db_queries_per_request` | Histogram | Queries per request | method, path |
| `db_query_threshold_exceeded` | Counter | Requests exceeding query threshold | method, path, count |

### Cache Metrics

| Metric | Type | Description | Labels |
|--------|------|-------------|--------|
| `cache_hits_total` | Counter | Total cache hits | key |
| `cache_misses_total` | Counter | Total cache misses | key |

## Integration with Monitoring Systems

### Prometheus

Configure Prometheus to scrape the metrics endpoint:

```yaml
scrape_configs:
  - job_name: 'api'
    static_configs:
      - targets: ['api.example.com']
    metrics_path: '/metrics'
    scrape_interval: 15s
```

### Grafana

Import the metrics into Grafana for visualization:

1. Add Prometheus as a data source
2. Create dashboards with queries like:
   - `rate(http_requests_total[5m])` - Request rate
   - `histogram_quantile(0.95, http_request_duration_seconds)` - 95th percentile latency
   - `cache_hits_total / (cache_hits_total + cache_misses_total)` - Cache hit ratio

## Best Practices

1. **Label Cardinality**: Keep label values bounded to avoid metric explosion
   - ✅ Good: `{method="GET", status="200"}`
   - ❌ Bad: `{user_id="12345"}` (unbounded)

2. **Metric Naming**: Follow Prometheus naming conventions
   - Use `_total` suffix for counters
   - Use `_seconds` suffix for durations
   - Use snake_case for names

3. **Performance**: Metrics collection has minimal overhead
   - Counters: O(1)
   - Gauges: O(1)
   - Histograms: O(1) per observation

4. **Query Thresholds**: Adjust based on your application
   - Default: 50 queries per request
   - Adjust in `DatabaseMetricsMiddleware` constructor

## Example Usage

```php
// In a controller
class OrderController
{
    private MetricsCollector $metrics;

    public function create(CreateOrderRequest $request): Response
    {
        $start = microtime(true);
        
        try {
            $order = $this->orderService->create($request);
            
            // Track successful order creation
            $this->metrics->increment('orders_created_total', [
                'status' => 'success',
                'payment_method' => $order->paymentMethod,
            ]);
            
            // Track order value
            $this->metrics->histogram('order_value_dollars', $order->total, [
                'payment_method' => $order->paymentMethod,
            ]);
            
            return ApiResponse::success($order);
        } catch (\Exception $e) {
            // Track failed order creation
            $this->metrics->increment('orders_created_total', [
                'status' => 'failed',
                'error' => get_class($e),
            ]);
            
            throw $e;
        } finally {
            // Track operation duration
            $duration = microtime(true) - $start;
            $this->metrics->timing('order_creation_duration_seconds', $duration);
        }
    }
}
```

## Troubleshooting

### Metrics not appearing

1. Check that `METRICS_ENABLED=true` in `.env`
2. Verify MetricsCollector is registered in the DI container
3. Ensure middleware is registered in the correct order

### High memory usage

1. Call `$metrics->reset()` periodically if running long-lived processes
2. Reduce label cardinality
3. Consider using a push gateway for long-running workers

### Query threshold warnings

1. Review the logged queries to identify N+1 problems
2. Add eager loading for relationships
3. Optimize queries or increase the threshold if appropriate
