# Health Check System

The Health Check System provides a standardized way to monitor the health of your application and its dependencies.

## Features

- ✅ Standardized health check interface
- ✅ Built-in checks for Database and Redis
- ✅ Support for custom health checks
- ✅ Response time measurement for each check
- ✅ Exception handling with automatic unhealthy status
- ✅ HTTP status codes (200 for healthy, 503 for unhealthy)
- ✅ JSON response format with detailed check results

## Components

### HealthCheckInterface

Interface that all health checks must implement:

```php
interface HealthCheckInterface
{
    public function check(): CheckResult;
}
```

### CheckResult

Represents the result of a health check:

```php
class CheckResult
{
    public function __construct(
        public readonly bool $healthy,
        public readonly ?string $message = null,
        public readonly array $metadata = []
    ) {}
}
```

### HealthChecker

Main class that manages and executes health checks:

```php
class HealthChecker
{
    public function addCheck(string $name, HealthCheckInterface $check): void;
    public function check(): array;
}
```

### Built-in Health Checks

#### DatabaseHealthCheck

Checks database connectivity using Doctrine EntityManager:

```php
$em = EntityManagerFactory::create();
$dbCheck = new DatabaseHealthCheck($em);
$healthChecker->addCheck('database', $dbCheck);
```

#### RedisHealthCheck

Checks Redis connectivity (optional):

```php
$redis = new Redis();
$redis->connect('localhost', 6379);
$redisCheck = new RedisHealthCheck($redis);
$healthChecker->addCheck('redis', $redisCheck);
```

## Usage

### Basic Setup

```php
use Framework\Health\HealthChecker;
use Framework\Health\DatabaseHealthCheck;
use Framework\Health\RedisHealthCheck;

// Create health checker
$healthChecker = new HealthChecker();

// Add database check
$healthChecker->addCheck('database', new DatabaseHealthCheck($em));

// Add Redis check (optional)
$healthChecker->addCheck('redis', new RedisHealthCheck($redis));

// Run health check
$result = $healthChecker->check();
```

### Custom Health Checks

Create custom health checks by implementing `HealthCheckInterface`:

```php
use Framework\Health\HealthCheckInterface;
use Framework\Health\CheckResult;

class DiskSpaceHealthCheck implements HealthCheckInterface
{
    public function check(): CheckResult
    {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        $usagePercent = (1 - ($freeSpace / $totalSpace)) * 100;
        
        $healthy = $usagePercent < 90;
        
        return new CheckResult(
            healthy: $healthy,
            message: $healthy ? 'Disk space is sufficient' : 'Disk space is low',
            metadata: [
                'free_space_gb' => round($freeSpace / (1024 ** 3), 2),
                'usage_percent' => round($usagePercent, 2),
            ]
        );
    }
}

// Add to health checker
$healthChecker->addCheck('disk_space', new DiskSpaceHealthCheck());
```

### Using with HealthController

The `HealthController` provides an HTTP endpoint for health checks:

```php
use Interface\Http\Controllers\HealthController;
use Framework\Http\Request;

$controller = new HealthController($healthChecker);
$response = $controller->check($request);

// Returns 200 for healthy, 503 for unhealthy
```

### Route Configuration

Add the health check endpoint to your routes:

```php
// config/routes.php
$router->get('/health', 'HealthController@check');
```

## Response Format

### Healthy Response (200 OK)

```json
{
  "status": "healthy",
  "checks": {
    "database": {
      "status": "healthy",
      "message": "Database connection is healthy",
      "metadata": {
        "driver": "pdo_mysql"
      },
      "response_time_ms": 5.23
    },
    "redis": {
      "status": "healthy",
      "message": "Redis connection is healthy",
      "metadata": [],
      "response_time_ms": 2.15
    }
  },
  "timestamp": "2025-11-08T11:02:10Z"
}
```

### Unhealthy Response (503 Service Unavailable)

```json
{
  "status": "unhealthy",
  "checks": {
    "database": {
      "status": "unhealthy",
      "message": "Database connection failed: Connection refused",
      "metadata": [],
      "response_time_ms": 2000.50
    },
    "redis": {
      "status": "healthy",
      "message": "Redis connection is healthy",
      "metadata": [],
      "response_time_ms": 2.15
    }
  },
  "timestamp": "2025-11-08T11:02:10Z"
}
```

## Testing

Run the health check tests:

```bash
# Basic health check system tests
php public/test-health-simple.php

# HealthController integration tests
php public/test-health-controller.php

# Full health check demo (requires database)
php public/test-health.php
```

## Integration with Monitoring Tools

### Load Balancers

Configure your load balancer to poll the `/health` endpoint:

- **Healthy**: 200 status code → route traffic to instance
- **Unhealthy**: 503 status code → remove instance from pool

### Monitoring Services

Set up monitoring alerts based on the health endpoint:

```bash
# Example with curl
curl http://localhost/health

# Check exit code
if [ $? -eq 0 ]; then
  echo "Service is healthy"
else
  echo "Service is unhealthy"
fi
```

### Kubernetes Liveness/Readiness Probes

```yaml
livenessProbe:
  httpGet:
    path: /health
    port: 80
  initialDelaySeconds: 30
  periodSeconds: 10

readinessProbe:
  httpGet:
    path: /health
    port: 80
  initialDelaySeconds: 5
  periodSeconds: 5
```

## Best Practices

1. **Keep checks fast**: Health checks should complete in < 1 second
2. **Check critical dependencies**: Database, cache, external APIs
3. **Use appropriate timeouts**: Don't let slow checks block the response
4. **Include metadata**: Provide useful diagnostic information
5. **Monitor response times**: Track check performance over time
6. **Separate liveness and readiness**: Consider different endpoints for different purposes

## Requirements Satisfied

This implementation satisfies the following requirements:

- **12.1**: Health check endpoint that returns system status ✅
- **12.2**: Database connectivity verification ✅
- **12.3**: Cache connectivity verification ✅
- **12.4**: 200 status for healthy, 503 for unhealthy ✅
- **12.5**: Detailed health check results with response times ✅

## Next Steps

1. Add health check endpoint to your production routes
2. Configure monitoring tools to poll the endpoint
3. Set up alerts for unhealthy status
4. Add custom health checks for external services
5. Integrate with load balancers for automatic failover
