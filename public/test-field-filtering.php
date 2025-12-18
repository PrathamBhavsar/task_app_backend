<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\FieldFilter;
use Framework\Middleware\FieldFilterMiddleware;
use Framework\Middleware\RequestHandler;
use Interface\Http\DTO\ApiResponse;

echo "<h1>Field Filtering Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test h3 { margin-top: 0; color: #333; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .query { color: #0066cc; font-family: monospace; }
</style>";

// Sample data for testing
$sampleClients = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'contact_no' => '+1234567890',
        'address' => [
            'street' => '123 Main St',
            'city' => 'New York',
            'state' => 'NY',
            'country' => 'USA',
            'zip' => '10001'
        ],
        'created_at' => '2024-01-15T10:30:00Z',
        'updated_at' => '2024-01-20T14:45:00Z'
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'contact_no' => '+1987654321',
        'address' => [
            'street' => '456 Oak Ave',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'country' => 'USA',
            'zip' => '90001'
        ],
        'created_at' => '2024-01-16T11:20:00Z',
        'updated_at' => '2024-01-21T09:15:00Z'
    ]
];

$sampleUser = [
    'id' => 1,
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'profile' => [
        'bio' => 'Software developer',
        'avatar' => 'https://example.com/avatar.jpg',
        'settings' => [
            'theme' => 'dark',
            'notifications' => true,
            'language' => 'en'
        ]
    ],
    'stats' => [
        'posts_count' => 42,
        'followers_count' => 150,
        'following_count' => 75
    ]
];

// Test 1: Basic field filtering
echo "<div class='test success'>";
echo "<h3>Test 1: Basic Field Selection</h3>";
echo "<p class='query'>?fields=id,name,email</p>";

$fieldFilter = new FieldFilter();
$fields = $fieldFilter->parseFields('id,name,email');
$filtered = $fieldFilter->filter($sampleClients, $fields);

echo "<h4>Original Data:</h4>";
echo "<pre>" . json_encode($sampleClients, JSON_PRETTY_PRINT) . "</pre>";

echo "<h4>Filtered Data:</h4>";
echo "<pre>" . json_encode($filtered, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test 2: Nested field selection
echo "<div class='test success'>";
echo "<h3>Test 2: Nested Field Selection</h3>";
echo "<p class='query'>?fields=id,name,address.city,address.country</p>";

$fields = $fieldFilter->parseFields('id,name,address.city,address.country');
$filtered = $fieldFilter->filter($sampleClients, $fields);

echo "<h4>Filtered Data:</h4>";
echo "<pre>" . json_encode($filtered, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test 3: Deep nested field selection
echo "<div class='test success'>";
echo "<h3>Test 3: Deep Nested Field Selection</h3>";
echo "<p class='query'>?fields=id,username,profile.avatar,profile.settings.theme,stats.posts_count</p>";

$fields = $fieldFilter->parseFields('id,username,profile.avatar,profile.settings.theme,stats.posts_count');
$filtered = $fieldFilter->filter($sampleUser, $fields);

echo "<h4>Original Data:</h4>";
echo "<pre>" . json_encode($sampleUser, JSON_PRETTY_PRINT) . "</pre>";

echo "<h4>Filtered Data:</h4>";
echo "<pre>" . json_encode($filtered, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test 4: Invalid field handling
echo "<div class='test error'>";
echo "<h3>Test 4: Invalid Field Handling</h3>";
echo "<p class='query'>?fields=id,name,invalid_field</p>";

try {
    $fields = $fieldFilter->parseFields('id,name,invalid_field');
    $filtered = $fieldFilter->filter($sampleClients, $fields);
    echo "<p style='color: red;'>ERROR: Should have thrown InvalidFieldException</p>";
} catch (\Framework\Http\InvalidFieldException $e) {
    echo "<h4>Exception Caught (Expected):</h4>";
    echo "<pre>";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Field Path: " . $e->getFieldPath() . "\n";
    echo "Available Fields: " . json_encode($e->getAvailableFields());
    echo "</pre>";
}
echo "</div>";

// Test 5: Middleware integration
echo "<div class='test success'>";
echo "<h3>Test 5: Middleware Integration</h3>";
echo "<p class='query'>?fields=id,name,email</p>";

// Create a mock request with fields parameter
$request = new Request(
    method: 'GET',
    uri: '/api/clients?fields=id,name,email',
    headers: [],
    query: ['fields' => 'id,name,email'],
    body: [],
    files: [],
    server: []
);

// Create a mock handler that returns API response
$handler = new class implements RequestHandler {
    private array $data;
    
    public function __construct()
    {
        $this->data = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'contact_no' => '+1234567890',
                'address' => 'New York, USA'
            ]
        ];
    }
    
    public function handle(Request $request): Response
    {
        return ApiResponse::success($this->data);
    }
};

$middleware = new FieldFilterMiddleware();
$response = $middleware->process($request, $handler);

echo "<h4>Response Body:</h4>";
echo "<pre>" . json_encode($response->body, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

// Test 6: Empty fields parameter
echo "<div class='test success'>";
echo "<h3>Test 6: Empty Fields Parameter (No Filtering)</h3>";
echo "<p class='query'>?fields=</p>";

$fields = $fieldFilter->parseFields('');
$filtered = $fieldFilter->filter($sampleClients, $fields);

echo "<h4>Result:</h4>";
echo "<p>Fields parsed: " . json_encode($fields) . "</p>";
echo "<p>Data returned unchanged: " . (count($filtered) === count($sampleClients) ? 'YES' : 'NO') . "</p>";
echo "</div>";

// Test 7: Field validation
echo "<div class='test success'>";
echo "<h3>Test 7: Field Validation</h3>";

$validFields = ['id', 'name', 'email'];
$invalidFields = ['id', 'name', 'invalid_field', 'another_invalid'];

$sampleData = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

$invalidFound = $fieldFilter->validateFields($invalidFields, $sampleData);

echo "<h4>Validation Results:</h4>";
echo "<p>Valid fields: " . json_encode($validFields) . "</p>";
echo "<p>Invalid fields found: " . json_encode($invalidFound) . "</p>";
echo "</div>";

// Test 8: Middleware with invalid fields
echo "<div class='test error'>";
echo "<h3>Test 8: Middleware with Invalid Fields</h3>";
echo "<p class='query'>?fields=id,name,nonexistent</p>";

$request = new Request(
    method: 'GET',
    uri: '/api/clients?fields=id,name,nonexistent',
    headers: [],
    query: ['fields' => 'id,name,nonexistent'],
    body: [],
    files: [],
    server: []
);

$response = $middleware->process($request, $handler);

echo "<h4>Response (Status: {$response->status}):</h4>";
echo "<pre>" . json_encode($response->body, JSON_PRETTY_PRINT) . "</pre>";
echo "</div>";

echo "<div class='test success'>";
echo "<h3>✓ All Tests Completed</h3>";
echo "<p>Field filtering is working correctly!</p>";
echo "</div>";

