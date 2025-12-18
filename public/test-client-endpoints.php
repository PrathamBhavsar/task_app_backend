<?php

/**
 * Test Client Endpoints with New Architecture
 * 
 * This script tests the migrated Client endpoints using the new
 * Framework architecture with Request/Response DTOs and validation.
 */

require_once __DIR__ . '/../bootstrap.php';

use Framework\Http\Request;
use Framework\Routing\Router;
use Framework\Container\Container;
use Framework\Middleware\MiddlewarePipeline;
use Framework\Middleware\ErrorHandlerMiddleware;
use Framework\Validation\Validator;
use Infrastructure\Database\EntityManagerFactory;
use Infrastructure\Persistence\Doctrine\ClientRepository;
use Interface\Controller\ClientController;
use Interface\Http\DTO\Request\CreateClientRequest;
use Interface\Http\DTO\Request\UpdateClientRequest;
use Application\UseCase\Client\{
    GetAllClientsUseCase,
    GetClientByIdUseCase,
    CreateClientUseCase,
    UpdateClientUseCase,
    DeleteClientUseCase
};

echo "=== Testing Client Endpoints with New Architecture ===\n\n";

// Initialize dependencies
$em = EntityManagerFactory::create();
$repo = new ClientRepository($em);

// Create controller with use cases
$controller = new ClientController(
    new GetAllClientsUseCase($repo),
    new GetClientByIdUseCase($repo),
    new CreateClientUseCase($repo),
    new UpdateClientUseCase($repo),
    new DeleteClientUseCase($repo)
);

// Test 1: Create a new client
echo "Test 1: Create a new client\n";
echo "----------------------------\n";

$createRequest = new Request(
    method: 'POST',
    uri: '/api/clients',
    headers: ['Content-Type' => 'application/json'],
    query: [],
    body: [
        'name' => 'Test Client ' . time(),
        'contact_no' => '+1234567890',
        'address' => '123 Test Street, Test City',
        'email' => 'test' . time() . '@example.com'
    ],
    files: [],
    server: []
);

try {
    $response = $controller->store($createRequest);
    echo "Status: " . $response->status . "\n";
    echo "Response: " . $response->toJson() . "\n";
    
    // Extract created client ID for further tests
    $responseData = json_decode($response->toJson(), true);
    $createdClientId = $responseData['data']['client_id'] ?? null;
    echo "Created Client ID: " . $createdClientId . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Get all clients
echo "Test 2: Get all clients\n";
echo "------------------------\n";

$indexRequest = new Request(
    method: 'GET',
    uri: '/api/clients',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: []
);

try {
    $response = $controller->index($indexRequest);
    echo "Status: " . $response->status . "\n";
    $responseData = json_decode($response->toJson(), true);
    $clientCount = count($responseData['data']['clients'] ?? []);
    echo "Total clients: " . $clientCount . "\n";
    echo "Response (first 500 chars): " . substr($response->toJson(), 0, 500) . "...\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Get a single client
if (isset($createdClientId)) {
    echo "Test 3: Get client by ID\n";
    echo "-------------------------\n";
    
    $showRequest = new Request(
        method: 'GET',
        uri: "/api/clients/{$createdClientId}",
        headers: [],
        query: [],
        body: [],
        files: [],
        server: [],
        attributes: ['id' => $createdClientId]
    );
    
    try {
        $response = $controller->show($showRequest);
        echo "Status: " . $response->status . "\n";
        echo "Response: " . $response->toJson() . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test 4: Update a client
if (isset($createdClientId)) {
    echo "Test 4: Update client\n";
    echo "----------------------\n";
    
    $updateRequest = new Request(
        method: 'PUT',
        uri: "/api/clients/{$createdClientId}",
        headers: ['Content-Type' => 'application/json'],
        query: [],
        body: [
            'name' => 'Updated Test Client',
            'address' => '456 Updated Street, New City'
        ],
        files: [],
        server: [],
        attributes: ['id' => $createdClientId]
    );
    
    try {
        $response = $controller->update($updateRequest);
        echo "Status: " . $response->status . "\n";
        echo "Response: " . $response->toJson() . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test 5: Validation error test
echo "Test 5: Test validation (invalid email)\n";
echo "----------------------------------------\n";

$invalidRequest = new Request(
    method: 'POST',
    uri: '/api/clients',
    headers: ['Content-Type' => 'application/json'],
    query: [],
    body: [
        'name' => 'Invalid Client',
        'contact_no' => '+1234567890',
        'address' => '789 Invalid Street',
        'email' => 'not-an-email' // Invalid email
    ],
    files: [],
    server: []
);

try {
    // Manually validate using the DTO
    $dto = CreateClientRequest::fromArray($invalidRequest->body);
    $validator = new Validator();
    $result = $validator->validate($dto);
    
    if (!$result->isValid) {
        echo "Validation failed (as expected):\n";
        echo json_encode($result->errors, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Validation passed (unexpected)\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 6: Test missing required fields
echo "Test 6: Test validation (missing required fields)\n";
echo "--------------------------------------------------\n";

$missingFieldsRequest = new Request(
    method: 'POST',
    uri: '/api/clients',
    headers: ['Content-Type' => 'application/json'],
    query: [],
    body: [
        'name' => 'Incomplete Client'
        // Missing contact_no, address, email
    ],
    files: [],
    server: []
);

try {
    $dto = CreateClientRequest::fromArray($missingFieldsRequest->body);
    $validator = new Validator();
    $result = $validator->validate($dto);
    
    if (!$result->isValid) {
        echo "Validation failed (as expected):\n";
        echo json_encode($result->errors, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Validation passed (unexpected)\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Delete a client
if (isset($createdClientId)) {
    echo "Test 7: Delete client\n";
    echo "----------------------\n";
    
    $deleteRequest = new Request(
        method: 'DELETE',
        uri: "/api/clients/{$createdClientId}",
        headers: [],
        query: [],
        body: [],
        files: [],
        server: [],
        attributes: ['id' => $createdClientId]
    );
    
    try {
        $response = $controller->destroy($deleteRequest);
        echo "Status: " . $response->status . "\n";
        echo "Response: " . $response->toJson() . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test 8: Test 404 for non-existent client
echo "Test 8: Test 404 for non-existent client\n";
echo "-----------------------------------------\n";

$notFoundRequest = new Request(
    method: 'GET',
    uri: '/api/clients/999999',
    headers: [],
    query: [],
    body: [],
    files: [],
    server: [],
    attributes: ['id' => 999999]
);

try {
    $response = $controller->show($notFoundRequest);
    echo "Status: " . $response->status . "\n";
    echo "Response: " . $response->toJson() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== All Tests Completed ===\n";
