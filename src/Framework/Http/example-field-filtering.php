<?php

declare(strict_types=1);

/**
 * Field Filtering Integration Examples
 * 
 * This file demonstrates how to integrate field filtering into your API.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\FieldFilter;
use Framework\Middleware\FieldFilterMiddleware;
use Framework\Middleware\MiddlewarePipeline;
use Framework\Middleware\RequestHandler;
use Interface\Http\DTO\ApiResponse;

// ============================================================================
// Example 1: Adding Field Filter Middleware to Pipeline
// ============================================================================

echo "Example 1: Middleware Pipeline Integration\n";
echo str_repeat("=", 60) . "\n\n";

// Create middleware pipeline
$pipeline = new MiddlewarePipeline();

// Add field filter middleware (should be added after auth but before response)
$pipeline->pipe(new FieldFilterMiddleware());

// Create a sample handler
$handler = new class implements RequestHandler {
    public function handle(Request $request): Response
    {
        $clients = [
            [
                'id' => 1,
                'name' => 'Acme Corp',
                'email' => 'contact@acme.com',
                'contact_no' => '+1234567890',
                'address' => [
                    'street' => '123 Business St',
                    'city' => 'New York',
                    'country' => 'USA'
                ],
                'created_at' => '2024-01-15T10:30:00Z'
            ]
        ];
        
        return ApiResponse::success($clients);
    }
};

// Simulate request with fields parameter
$request = new Request(
    method: 'GET',
    uri: '/api/clients?fields=id,name,address.city',
    headers: [],
    query: ['fields' => 'id,name,address.city'],
    body: [],
    files: [],
    server: []
);

$response = $pipeline->process($request, $handler);
echo "Response:\n";
echo json_encode($response->body, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// Example 2: Controller-Level Field Filtering
// ============================================================================

echo "Example 2: Controller-Level Field Filtering\n";
echo str_repeat("=", 60) . "\n\n";

class ClientController
{
    private FieldFilter $fieldFilter;

    public function __construct()
    {
        $this->fieldFilter = new FieldFilter();
    }

    public function index(Request $request): Response
    {
        // Fetch data from repository
        $clients = $this->getClientsFromDatabase();
        
        // Check if fields parameter is present
        $fieldsParam = $request->query['fields'] ?? null;
        
        if ($fieldsParam !== null) {
            try {
                $fields = $this->fieldFilter->parseFields($fieldsParam);
                
                if (!empty($fields)) {
                    $clients = $this->fieldFilter->filter($clients, $fields);
                }
            } catch (\Framework\Http\InvalidFieldException $e) {
                return ApiResponse::error(
                    $e->getMessage(),
                    400,
                    'INVALID_FIELD',
                    null,
                    [
                        'field' => $e->getFieldPath(),
                        'available_fields' => $e->getAvailableFields()
                    ]
                );
            }
        }
        
        return ApiResponse::success($clients);
    }

    private function getClientsFromDatabase(): array
    {
        // Simulated database fetch
        return [
            [
                'id' => 1,
                'name' => 'Client A',
                'email' => 'clienta@example.com',
                'contact_no' => '+1111111111',
                'address' => 'New York, USA'
            ],
            [
                'id' => 2,
                'name' => 'Client B',
                'email' => 'clientb@example.com',
                'contact_no' => '+2222222222',
                'address' => 'Los Angeles, USA'
            ]
        ];
    }
}

$controller = new ClientController();
$request = new Request(
    method: 'GET',
    uri: '/api/clients?fields=id,name,email',
    headers: [],
    query: ['fields' => 'id,name,email'],
    body: [],
    files: [],
    server: []
);

$response = $controller->index($request);
echo "Response:\n";
echo json_encode($response->body, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// Example 3: Field Validation Before Processing
// ============================================================================

echo "Example 3: Field Validation Before Processing\n";
echo str_repeat("=", 60) . "\n\n";

$fieldFilter = new FieldFilter();

// Sample data structure
$sampleData = [
    'id' => 1,
    'name' => 'Test',
    'email' => 'test@example.com',
    'profile' => [
        'bio' => 'Developer',
        'avatar' => 'avatar.jpg'
    ]
];

// Requested fields
$requestedFields = ['id', 'name', 'profile.bio', 'invalid_field'];

// Validate fields
$invalidFields = $fieldFilter->validateFields($requestedFields, $sampleData);

if (!empty($invalidFields)) {
    echo "Invalid fields detected: " . implode(', ', $invalidFields) . "\n";
    echo "Available fields: " . implode(', ', array_keys($sampleData)) . "\n\n";
} else {
    echo "All fields are valid!\n\n";
}

// ============================================================================
// Example 4: Route Configuration with Field Filtering
// ============================================================================

echo "Example 4: Route Configuration\n";
echo str_repeat("=", 60) . "\n\n";

echo "In your route configuration file (config/routes/api.php):\n\n";
echo <<<'PHP'
<?php

use Framework\Routing\Router;
use Framework\Middleware\FieldFilterMiddleware;

return function (Router $router) {
    // Apply field filtering to all API routes
    $router->addGroup('/api', function (Router $router) {
        
        // Client routes with field filtering
        $router->get('/clients', 'Interface\Http\Controllers\ClientController@index');
        $router->get('/clients/{id}', 'Interface\Http\Controllers\ClientController@show');
        
        // User routes with field filtering
        $router->get('/users', 'Interface\Http\Controllers\UserController@index');
        $router->get('/users/{id}', 'Interface\Http\Controllers\UserController@show');
        
    }, [
        new FieldFilterMiddleware()
    ]);
};

PHP;

echo "\n\n";

// ============================================================================
// Example 5: Complex Nested Field Selection
// ============================================================================

echo "Example 5: Complex Nested Field Selection\n";
echo str_repeat("=", 60) . "\n\n";

$complexData = [
    'id' => 1,
    'order_number' => 'ORD-2024-001',
    'status' => 'completed',
    'customer' => [
        'id' => 123,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'address' => [
            'street' => '123 Main St',
            'city' => 'New York',
            'country' => 'USA'
        ]
    ],
    'items' => [
        [
            'id' => 1,
            'product' => [
                'id' => 456,
                'name' => 'Widget',
                'price' => 29.99
            ],
            'quantity' => 2
        ],
        [
            'id' => 2,
            'product' => [
                'id' => 789,
                'name' => 'Gadget',
                'price' => 49.99
            ],
            'quantity' => 1
        ]
    ],
    'total' => 109.97,
    'created_at' => '2024-01-15T10:30:00Z'
];

$fields = $fieldFilter->parseFields('id,order_number,customer.name,customer.email,items.product.name,items.quantity,total');
$filtered = $fieldFilter->filter($complexData, $fields);

echo "Original data has " . count($complexData) . " top-level fields\n";
echo "Filtered data:\n";
echo json_encode($filtered, JSON_PRETTY_PRINT) . "\n\n";

// ============================================================================
// Example 6: Performance Optimization with Field Filtering
// ============================================================================

echo "Example 6: Performance Optimization\n";
echo str_repeat("=", 60) . "\n\n";

echo "Benefits of field filtering:\n";
echo "- Reduced payload size (faster network transfer)\n";
echo "- Less data for clients to parse\n";
echo "- Lower bandwidth costs\n";
echo "- Better mobile experience\n\n";

// Simulate large dataset
$largeDataset = array_map(function($i) {
    return [
        'id' => $i,
        'name' => "Client $i",
        'email' => "client$i@example.com",
        'contact_no' => "+123456789$i",
        'address' => "Address $i",
        'description' => str_repeat("Lorem ipsum dolor sit amet. ", 20),
        'metadata' => [
            'created_at' => '2024-01-15T10:30:00Z',
            'updated_at' => '2024-01-20T14:45:00Z',
            'created_by' => 'admin',
            'updated_by' => 'admin'
        ]
    ];
}, range(1, 100));

$fullSize = strlen(json_encode($largeDataset));

$fields = $fieldFilter->parseFields('id,name,email');
$filtered = $fieldFilter->filter($largeDataset, $fields);
$filteredSize = strlen(json_encode($filtered));

$reduction = round((1 - $filteredSize / $fullSize) * 100, 2);

echo "Full dataset size: " . number_format($fullSize) . " bytes\n";
echo "Filtered dataset size: " . number_format($filteredSize) . " bytes\n";
echo "Size reduction: {$reduction}%\n\n";

echo "✓ All examples completed successfully!\n";

