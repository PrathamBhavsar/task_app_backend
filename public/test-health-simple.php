<?php

/**
 * Simple Health Check System Test (No Database Required)
 * 
 * This script tests the health check system without requiring database credentials.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Health\HealthChecker;
use Framework\Health\CheckResult;
use Framework\Health\HealthCheckInterface;

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

echo "=== Health Check System Tests ===\n\n";

// Test 1: CheckResult class
echo "Test 1: CheckResult class\n";
$result = new CheckResult(true, 'Test message', ['key' => 'value']);
test('CheckResult healthy property', $result->healthy === true);
test('CheckResult message property', $result->message === 'Test message');
test('CheckResult metadata property', $result->metadata === ['key' => 'value']);
$array = $result->toArray();
test('CheckResult toArray status', $array['status'] === 'healthy');
test('CheckResult toArray message', $array['message'] === 'Test message');
test('CheckResult toArray metadata', $array['metadata'] === ['key' => 'value']);
echo "\n";

// Test 2: CheckResult unhealthy
echo "Test 2: CheckResult unhealthy state\n";
$unhealthyResult = new CheckResult(false, 'Error message');
test('CheckResult unhealthy property', $unhealthyResult->healthy === false);
$unhealthyArray = $unhealthyResult->toArray();
test('CheckResult unhealthy toArray status', $unhealthyArray['status'] === 'unhealthy');
echo "\n";

// Test 3: HealthChecker empty
echo "Test 3: HealthChecker with no checks\n";
$checker = new HealthChecker();
$result = $checker->check();
test('Empty HealthChecker returns healthy', $result['status'] === 'healthy');
test('Empty HealthChecker has empty checks', $result['checks'] === []);
test('Empty HealthChecker has timestamp', isset($result['timestamp']));
echo "\n";

// Test 4: Custom healthy check
echo "Test 4: Adding healthy check\n";
$healthyCheck = new class implements HealthCheckInterface {
    public function check(): CheckResult {
        return new CheckResult(true, 'Service is healthy');
    }
};
$checker->addCheck('test_service', $healthyCheck);
$result = $checker->check();
test('HealthChecker with healthy check returns healthy', $result['status'] === 'healthy');
test('HealthChecker includes check result', isset($result['checks']['test_service']));
test('Check result has correct status', $result['checks']['test_service']['status'] === 'healthy');
test('Check result has response time', isset($result['checks']['test_service']['response_time_ms']));
echo "\n";

// Test 5: Custom unhealthy check
echo "Test 5: Adding unhealthy check\n";
$unhealthyCheck = new class implements HealthCheckInterface {
    public function check(): CheckResult {
        return new CheckResult(false, 'Service is down');
    }
};
$checker2 = new HealthChecker();
$checker2->addCheck('failing_service', $unhealthyCheck);
$result = $checker2->check();
test('HealthChecker with unhealthy check returns unhealthy', $result['status'] === 'unhealthy');
test('Unhealthy check has correct status', $result['checks']['failing_service']['status'] === 'unhealthy');
test('Unhealthy check has error message', $result['checks']['failing_service']['message'] === 'Service is down');
echo "\n";

// Test 6: Multiple checks
echo "Test 6: Multiple health checks\n";
$checker3 = new HealthChecker();
$checker3->addCheck('service1', $healthyCheck);
$checker3->addCheck('service2', $healthyCheck);
$checker3->addCheck('service3', $healthyCheck);
$result = $checker3->check();
test('Multiple healthy checks return healthy', $result['status'] === 'healthy');
test('All checks are included', count($result['checks']) === 3);
echo "\n";

// Test 7: Mixed healthy and unhealthy
echo "Test 7: Mixed healthy and unhealthy checks\n";
$checker4 = new HealthChecker();
$checker4->addCheck('healthy_service', $healthyCheck);
$checker4->addCheck('unhealthy_service', $unhealthyCheck);
$result = $checker4->check();
test('Mixed checks return unhealthy overall', $result['status'] === 'unhealthy');
test('Healthy service shows healthy', $result['checks']['healthy_service']['status'] === 'healthy');
test('Unhealthy service shows unhealthy', $result['checks']['unhealthy_service']['status'] === 'unhealthy');
echo "\n";

// Test 8: Exception handling
echo "Test 8: Exception handling in health checks\n";
$throwingCheck = new class implements HealthCheckInterface {
    public function check(): CheckResult {
        throw new \Exception('Unexpected error');
    }
};
$checker5 = new HealthChecker();
$checker5->addCheck('throwing_service', $throwingCheck);
$result = $checker5->check();
test('Exception in check returns unhealthy', $result['status'] === 'unhealthy');
test('Exception message is captured', $result['checks']['throwing_service']['message'] === 'Unexpected error');
test('Exception sets status to unhealthy', $result['checks']['throwing_service']['status'] === 'unhealthy');
echo "\n";

// Test 9: Response time measurement
echo "Test 9: Response time measurement\n";
$slowCheck = new class implements HealthCheckInterface {
    public function check(): CheckResult {
        usleep(10000); // Sleep for 10ms
        return new CheckResult(true, 'Slow service');
    }
};
$checker6 = new HealthChecker();
$checker6->addCheck('slow_service', $slowCheck);
$result = $checker6->check();
test('Response time is measured', isset($result['checks']['slow_service']['response_time_ms']));
test('Response time is reasonable', $result['checks']['slow_service']['response_time_ms'] >= 5); // At least 5ms
echo "\n";

// Test 10: HealthController simulation
echo "Test 10: HealthController HTTP status codes\n";
$healthyChecker = new HealthChecker();
$healthyChecker->addCheck('service', $healthyCheck);
$healthyResult = $healthyChecker->check();
$healthyStatus = $healthyResult['status'] === 'healthy' ? 200 : 503;
test('Healthy system returns 200', $healthyStatus === 200);

$unhealthyChecker = new HealthChecker();
$unhealthyChecker->addCheck('service', $unhealthyCheck);
$unhealthyResult = $unhealthyChecker->check();
$unhealthyStatus = $unhealthyResult['status'] === 'healthy' ? 200 : 503;
test('Unhealthy system returns 503', $unhealthyStatus === 503);
echo "\n";

// Summary
echo "=== Test Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed!\n";
    exit(1);
}
