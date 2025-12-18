<?php

/**
 * Health Controller Integration Test
 * 
 * This script tests the HealthController with the Framework routing system.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Health\HealthChecker;
use Framework\Health\CheckResult;
use Framework\Health\HealthCheckInterface;
use Framework\Http\Request;
use Interface\Http\Controllers\HealthController;

// Test counter
$passed = 0;
$failed = 0;

function test($name, $condition, $message = '') {
    global $passed, $failed;
    if ($condition) {
        echo "✓ PASS: $name\n";
        $passed++;
    } else {
        echo "✗ FAIL: $name" . ($message ? " - $message" : "") . "\n";
        $failed++;
    }
}

echo "=== Health Controller Integration Tests ===\n\n";

// Test 1: HealthController with healthy system
echo "Test 1: HealthController with healthy system\n";
$healthyCheck = new class implements HealthCheckInterface {
    public function check(): CheckResult {
        return new CheckResult(true, 'Service is healthy');
    }
};

$healthChecker = new HealthChecker();
$healthChecker->addCheck('test_service', $healthyCheck);

$controller = new HealthController($healthChecker);
$request = new Request('GET', '/health', [], [], [], [], []);
$response = $controller->check($request);

test('Healthy system returns 200 status', $response->status === 200);
test('Response has Content-Type header', $response->getHeader('Content-Type') === 'application/json');

$body = json_decode($response->toJson(), true);
test('Response body has status field', isset($body['status']));
test('Response status is healthy', $body['status'] === 'healthy');
test('Response has checks field', isset($body['checks']));
test('Response has timestamp field', isset($body['timestamp']));
echo "\n";

// Test 2: HealthController with unhealthy system
echo "Test 2: HealthController with unhealthy system\n";
$unhealthyCheck = new class implements HealthCheckInterface {
    public function check(): CheckResult {
        return new CheckResult(false, 'Service is down');
    }
};

$unhealthyChecker = new HealthChecker();
$unhealthyChecker->addCheck('failing_service', $unhealthyCheck);

$controller2 = new HealthController($unhealthyChecker);
$response2 = $controller2->check($request);

test('Unhealthy system returns 503 status', $response2->status === 503);
test('Response has Content-Type header', $response2->getHeader('Content-Type') === 'application/json');

$body2 = json_decode($response2->toJson(), true);
test('Response status is unhealthy', $body2['status'] === 'unhealthy');
test('Response includes failing check', isset($body2['checks']['failing_service']));
test('Failing check has unhealthy status', $body2['checks']['failing_service']['status'] === 'unhealthy');
echo "\n";

// Test 3: HealthController with multiple checks
echo "Test 3: HealthController with multiple checks\n";
$multiChecker = new HealthChecker();
$multiChecker->addCheck('service1', $healthyCheck);
$multiChecker->addCheck('service2', $healthyCheck);
$multiChecker->addCheck('service3', $unhealthyCheck);

$controller3 = new HealthController($multiChecker);
$response3 = $controller3->check($request);

test('Mixed checks return 503 status', $response3->status === 503);

$body3 = json_decode($response3->toJson(), true);
test('Response has all checks', count($body3['checks']) === 3);
test('Overall status is unhealthy', $body3['status'] === 'unhealthy');
test('Service1 is healthy', $body3['checks']['service1']['status'] === 'healthy');
test('Service2 is healthy', $body3['checks']['service2']['status'] === 'healthy');
test('Service3 is unhealthy', $body3['checks']['service3']['status'] === 'unhealthy');
echo "\n";

// Test 4: Response format validation
echo "Test 4: Response format validation\n";
$formatChecker = new HealthChecker();
$formatChecker->addCheck('test', $healthyCheck);

$controller4 = new HealthController($formatChecker);
$response4 = $controller4->check($request);
$body4 = json_decode($response4->toJson(), true);

test('Response is valid JSON', json_last_error() === JSON_ERROR_NONE);
test('Status field is string', is_string($body4['status']));
test('Checks field is array', is_array($body4['checks']));
test('Timestamp field is string', is_string($body4['timestamp']));
test('Check has response_time_ms', isset($body4['checks']['test']['response_time_ms']));
test('Response time is numeric', is_numeric($body4['checks']['test']['response_time_ms']));
echo "\n";

// Test 5: Empty health checker
echo "Test 5: HealthController with no checks\n";
$emptyChecker = new HealthChecker();
$controller5 = new HealthController($emptyChecker);
$response5 = $controller5->check($request);

test('Empty checker returns 200 status', $response5->status === 200);

$body5 = json_decode($response5->toJson(), true);
test('Empty checker status is healthy', $body5['status'] === 'healthy');
test('Empty checker has empty checks array', $body5['checks'] === []);
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    echo "\nThe HealthController is ready to use!\n";
    echo "Add it to your routes: GET /health -> HealthController@check\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed!\n";
    exit(1);
}
