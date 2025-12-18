<?php

/**
 * Example: Using Custom Business Metrics
 * 
 * This example demonstrates how to register and use custom business metrics
 * to track application-specific events and measurements.
 */

use Framework\Metrics\MetricsCollector;

$metrics = new MetricsCollector();

// ============================================================================
// Example 1: E-commerce Metrics
// ============================================================================

// Register custom metrics for an e-commerce application
$metrics->registerCustomMetric(
    name: 'orders_created',
    type: 'counter',
    description: 'Total number of orders created',
    labels: ['status' => 'pending']
);

$metrics->registerCustomMetric(
    name: 'order_value',
    type: 'histogram',
    description: 'Order value in dollars',
    labels: []
);

$metrics->registerCustomMetric(
    name: 'cart_abandonment',
    type: 'counter',
    description: 'Number of abandoned shopping carts',
    labels: []
);

// Track order creation
$metrics->recordCustomMetric('orders_created', 1, ['status' => 'completed', 'payment_method' => 'credit_card']);
$metrics->recordCustomMetric('order_value', 149.99, ['payment_method' => 'credit_card']);

// Track cart abandonment
$metrics->increment('cart_abandonment', ['reason' => 'timeout']);

// ============================================================================
// Example 2: User Activity Metrics
// ============================================================================

$metrics->registerCustomMetric(
    name: 'user_logins',
    type: 'counter',
    description: 'Total number of user logins',
    labels: []
);

$metrics->registerCustomMetric(
    name: 'active_sessions',
    type: 'gauge',
    description: 'Number of currently active user sessions',
    labels: []
);

$metrics->registerCustomMetric(
    name: 'session_duration',
    type: 'histogram',
    description: 'User session duration in seconds',
    labels: []
);

// Track user login
$metrics->increment('user_logins', ['method' => 'password', 'success' => 'true']);

// Update active sessions
$metrics->gauge('active_sessions', 42.0);

// Track session duration when user logs out
$metrics->histogram('session_duration', 1800.0, ['user_type' => 'premium']);

// ============================================================================
// Example 3: API Usage Metrics
// ============================================================================

$metrics->registerCustomMetric(
    name: 'api_calls',
    type: 'counter',
    description: 'Total number of API calls by client',
    labels: []
);

$metrics->registerCustomMetric(
    name: 'api_quota_remaining',
    type: 'gauge',
    description: 'Remaining API quota for client',
    labels: []
);

// Track API usage
$metrics->increment('api_calls', ['client_id' => 'client_123', 'endpoint' => '/api/users']);
$metrics->gauge('api_quota_remaining', 9500.0, ['client_id' => 'client_123']);

// ============================================================================
// Example 4: Business Process Metrics
// ============================================================================

$metrics->registerCustomMetric(
    name: 'document_processing',
    type: 'counter',
    description: 'Documents processed by type',
    labels: []
);

$metrics->registerCustomMetric(
    name: 'processing_duration',
    type: 'histogram',
    description: 'Document processing duration in seconds',
    labels: []
);

$metrics->registerCustomMetric(
    name: 'processing_errors',
    type: 'counter',
    description: 'Document processing errors',
    labels: []
);

// Track document processing
$start = microtime(true);
try {
    // Process document...
    $metrics->increment('document_processing', ['type' => 'invoice', 'status' => 'success']);
} catch (\Exception $e) {
    $metrics->increment('processing_errors', ['type' => 'invoice', 'error' => get_class($e)]);
} finally {
    $duration = microtime(true) - $start;
    $metrics->histogram('processing_duration', $duration, ['type' => 'invoice']);
}

// ============================================================================
// Example 5: Using Metrics in a Controller
// ============================================================================

use Framework\Http\Request;
use Framework\Http\Response;

class OrderController
{
    private MetricsCollector $metrics;
    private OrderService $orderService;

    public function __construct(MetricsCollector $metrics, OrderService $orderService)
    {
        $this->metrics = $metrics;
        $this->orderService = $orderService;
    }

    public function create(Request $request): Response
    {
        $start = microtime(true);
        
        try {
            $order = $this->orderService->create($request->body);
            
            // Track successful order
            $this->metrics->increment('orders_created', [
                'status' => 'success',
                'payment_method' => $order->paymentMethod,
                'country' => $order->shippingAddress->country,
            ]);
            
            // Track order value
            $this->metrics->histogram('order_value', $order->total, [
                'payment_method' => $order->paymentMethod,
            ]);
            
            // Track items per order
            $this->metrics->histogram('order_items_count', count($order->items), [
                'payment_method' => $order->paymentMethod,
            ]);
            
            return new Response(['order' => $order], 201);
            
        } catch (PaymentFailedException $e) {
            // Track payment failure
            $this->metrics->increment('orders_created', [
                'status' => 'payment_failed',
                'payment_method' => $request->body['payment_method'] ?? 'unknown',
            ]);
            
            throw $e;
            
        } catch (\Exception $e) {
            // Track general failure
            $this->metrics->increment('orders_created', [
                'status' => 'failed',
                'error' => get_class($e),
            ]);
            
            throw $e;
            
        } finally {
            // Always track duration
            $duration = microtime(true) - $start;
            $this->metrics->timing('order_creation_duration', $duration);
        }
    }
}

// ============================================================================
// Example 6: Tracking Background Job Metrics
// ============================================================================

use Framework\Queue\JobInterface;

class SendEmailJob implements JobInterface
{
    private MetricsCollector $metrics;
    private string $to;
    private string $subject;

    public function handle(): void
    {
        $start = microtime(true);
        
        try {
            // Send email...
            
            $this->metrics->increment('emails_sent', [
                'type' => 'transactional',
                'status' => 'success',
            ]);
            
        } catch (\Exception $e) {
            $this->metrics->increment('emails_sent', [
                'type' => 'transactional',
                'status' => 'failed',
                'error' => get_class($e),
            ]);
            
            throw $e;
            
        } finally {
            $duration = microtime(true) - $start;
            $this->metrics->timing('email_send_duration', $duration, [
                'type' => 'transactional',
            ]);
        }
    }
}

// ============================================================================
// Best Practices
// ============================================================================

/*
1. Keep label cardinality low
   ✅ Good: ['status' => 'success', 'payment_method' => 'credit_card']
   ❌ Bad: ['user_id' => '12345', 'order_id' => '67890'] (unbounded)

2. Use consistent naming
   - Use snake_case for metric names
   - Use descriptive names that indicate what is being measured
   - Add units to the name (e.g., _seconds, _bytes, _total)

3. Choose the right metric type
   - Counter: Things that only increase (requests, errors, orders)
   - Gauge: Things that can go up or down (active users, queue size)
   - Histogram: Distributions (request duration, order value)

4. Register metrics at startup
   - Register all custom metrics in a ServiceProvider
   - This provides documentation and validation

5. Track both success and failure
   - Always track the outcome of operations
   - Include error types in labels for debugging

6. Measure duration consistently
   - Use try-finally blocks to ensure duration is always recorded
   - Track duration even for failed operations
*/
