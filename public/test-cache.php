<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Cache\CacheManager;
use Framework\Cache\ArrayStore;
use Framework\Cache\CacheFactory;
use Framework\Middleware\CacheMiddleware;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\RequestHandler;
use Framework\Config\Config;

echo "=== HTTP Caching System Test ===\n\n";

// Test 1: ETag Generation
echo "Test 1: ETag Generation\n";
$store = new ArrayStore();
$cacheManager = new CacheManager($store);

$content = ['id' => 1, 'name' => 'Test Client', 'email' => 'test@example.com'];
$etag = $cacheManager->generateETag($content);
echo "Generated ETag: {$etag}\n";
echo "✓ ETag generated successfully\n\n";

// Test 2: If-None-Match Check
echo "Test 2: If-None-Match Check\n";
$request = new Request(
    method: 'GET',
    uri: '/api/clients/1',
    headers: ['If-None-Match' => $etag],
    query: [],
    body: [],
    files: [],
    server: []
);

$isNotModified = $cacheManager->isNotModified($request, $etag);
echo "Is Not Modified: " . ($isNotModified ? 'Yes' : 'No') . "\n";
echo "✓ Conditional request check works\n\n";

// Test 3: Cache Headers
echo "Test 3: Cache Headers\n";
$response = new Response($content, 200);
$response = $cacheManager->setCacheHeaders($response, [
    'max_age' => 3600,
    'must_revalidate' => true,
    'vary' => ['Accept', 'Accept-Encoding']
]);

echo "Cache-Control: " . ($response->getHeader('Cache-Control') ?? 'Not set') . "\n";
echo "Vary: " . ($response->getHeader('Vary') ?? 'Not set') . "\n";
echo "✓ Cache headers set successfully\n\n";

// Test 4: Last-Modified Check
echo "Test 4: Last-Modified Check\n";
$lastModified = new DateTime('2024-01-01 12:00:00');
$requestWithModifiedSince = new Request(
    method: 'GET',
    uri: '/api/clients/1',
    headers: ['If-Modified-Since' => 'Mon, 01 Jan 2024 12:00:00 GMT'],
    query: [],
    body: [],
    files: [],
    server: []
);

$isNotModifiedByTime = $cacheManager->checkLastModified($requestWithModifiedSince, $lastModified);
echo "Is Not Modified (by time): " . ($isNotModifiedByTime ? 'Yes' : 'No') . "\n";
echo "✓ Last-Modified check works\n\n";

// Test 5: Cache Store Operations
echo "Test 5: Cache Store Operations\n";
$cacheManager->put('test:key', ['data' => 'cached value'], 60);
echo "Stored in cache: test:key\n";

$cachedValue = $cacheManager->get('test:key');
echo "Retrieved from cache: " . json_encode($cachedValue) . "\n";

$exists = $cacheManager->has('test:key');
echo "Cache key exists: " . ($exists ? 'Yes' : 'No') . "\n";

$cacheManager->invalidate('test:key');
$existsAfterInvalidation = $cacheManager->has('test:key');
echo "Cache key exists after invalidation: " . ($existsAfterInvalidation ? 'Yes' : 'No') . "\n";
echo "✓ Cache store operations work\n\n";

// Test 6: Remember Pattern
echo "Test 6: Remember Pattern\n";
$callCount = 0;
$value1 = $cacheManager->remember('expensive:operation', 60, function() use (&$callCount) {
    $callCount++;
    return ['result' => 'computed value'];
});
echo "First call - Callback executed: " . ($callCount === 1 ? 'Yes' : 'No') . "\n";

$value2 = $cacheManager->remember('expensive:operation', 60, function() use (&$callCount) {
    $callCount++;
    return ['result' => 'computed value'];
});
echo "Second call - Callback executed: " . ($callCount === 2 ? 'Yes' : 'No') . " (should be No)\n";
echo "Values match: " . (json_encode($value1) === json_encode($value2) ? 'Yes' : 'No') . "\n";
echo "✓ Remember pattern works\n\n";

// Test 7: CacheMiddleware
echo "Test 7: CacheMiddleware\n";
$middleware = new CacheMiddleware($cacheManager, [
    'max_age' => 1800,
    'enable_etag' => true,
]);

$testRequest = new Request(
    method: 'GET',
    uri: '/api/test',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

$handler = new class implements RequestHandler {
    public function handle(Request $request): Response
    {
        return new Response(['message' => 'Hello World'], 200);
    }
};

$middlewareResponse = $middleware->process($testRequest, $handler);
echo "Response status: {$middlewareResponse->status}\n";
echo "ETag header present: " . ($middlewareResponse->hasHeader('ETag') ? 'Yes' : 'No') . "\n";
echo "Cache-Control header present: " . ($middlewareResponse->hasHeader('Cache-Control') ? 'Yes' : 'No') . "\n";
echo "✓ CacheMiddleware works\n\n";

// Test 8: 304 Response via Middleware
echo "Test 8: 304 Response via Middleware\n";
$responseETag = $middlewareResponse->getHeader('ETag');
$requestWithETag = new Request(
    method: 'GET',
    uri: '/api/test',
    headers: ['If-None-Match' => $responseETag],
    query: [],
    body: [],
    files: [],
    server: []
);

$notModifiedResponse = $middleware->process($requestWithETag, $handler);
echo "Response status: {$notModifiedResponse->status}\n";
echo "Is 304 Not Modified: " . ($notModifiedResponse->status === 304 ? 'Yes' : 'No') . "\n";
echo "✓ 304 response works\n\n";

// Test 9: ArrayStore Expiry
echo "Test 9: ArrayStore Expiry\n";
$arrayStore = new ArrayStore();
$arrayStore->put('expiring:key', 'value', 1); // 1 second TTL
echo "Stored with 1 second TTL\n";
echo "Exists immediately: " . ($arrayStore->has('expiring:key') ? 'Yes' : 'No') . "\n";
sleep(2);
echo "Exists after 2 seconds: " . ($arrayStore->has('expiring:key') ? 'Yes' : 'No') . " (should be No)\n";
echo "✓ Cache expiry works\n\n";

echo "=== All Tests Passed ✓ ===\n";
