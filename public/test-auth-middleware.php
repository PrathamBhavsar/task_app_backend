<?php

declare(strict_types=1);

// Load environment and autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Framework\Config\EnvLoader;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Middleware\AuthenticationMiddleware;
use Framework\Middleware\RequestHandler;
use Framework\Error\UnauthorizedException;
use Infrastructure\Auth\JwtService;
use Domain\Entity\User;

// Load environment variables
$envLoader = new EnvLoader();
$envLoader->load(__DIR__ . '/../.env');

// Create JWT service
$jwtService = new JwtService();

// Create a test user
$testUser = new User(
    name: 'Test User',
    contactNo: '1234567890',
    address: '123 Test St',
    email: 'test@example.com',
    user_type: 'admin',
    profile_bg_color: '#000000'
);

// Set user ID using reflection (since it's auto-generated in real scenario)
$reflection = new ReflectionClass($testUser);
$idProperty = $reflection->getProperty('user_id');
$idProperty->setAccessible(true);
$idProperty->setValue($testUser, 1);

// Generate test tokens
$tokens = $jwtService->generateTokens($testUser);

echo "=== Authentication Middleware Test ===\n\n";

// Test 1: Request without token (should fail)
echo "Test 1: Request without Authorization header\n";
try {
    $request = new Request(
        method: 'GET',
        uri: '/api/protected',
        headers: [],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $middleware = new AuthenticationMiddleware($jwtService);
    $handler = new class implements RequestHandler {
        public function handle(Request $request): Response {
            return new Response(['message' => 'Protected resource accessed']);
        }
    };
    
    $response = $middleware->process($request, $handler);
    echo "❌ FAILED: Should have thrown UnauthorizedException\n\n";
} catch (UnauthorizedException $e) {
    echo "✅ PASSED: " . $e->getMessage() . "\n\n";
}

// Test 2: Request with valid token (should succeed)
echo "Test 2: Request with valid Authorization token\n";
try {
    $request = new Request(
        method: 'GET',
        uri: '/api/protected',
        headers: ['Authorization' => 'Bearer ' . $tokens['access_token']],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $middleware = new AuthenticationMiddleware($jwtService);
    $handler = new class implements RequestHandler {
        public function handle(Request $request): Response {
            $userId = $request->getAttribute('user_id');
            $userEmail = $request->getAttribute('user_email');
            return new Response([
                'message' => 'Protected resource accessed',
                'user_id' => $userId,
                'user_email' => $userEmail
            ]);
        }
    };
    
    $response = $middleware->process($request, $handler);
    $body = json_decode($response->toJson(), true);
    
    if ($body['user_id'] === 1 && $body['user_email'] === 'test@example.com') {
        echo "✅ PASSED: User authenticated successfully\n";
        echo "   User ID: " . $body['user_id'] . "\n";
        echo "   User Email: " . $body['user_email'] . "\n\n";
    } else {
        echo "❌ FAILED: User attributes not set correctly\n\n";
    }
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 3: Request with invalid token (should fail)
echo "Test 3: Request with invalid token\n";
try {
    $request = new Request(
        method: 'GET',
        uri: '/api/protected',
        headers: ['Authorization' => 'Bearer invalid-token-here'],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $middleware = new AuthenticationMiddleware($jwtService);
    $handler = new class implements RequestHandler {
        public function handle(Request $request): Response {
            return new Response(['message' => 'Protected resource accessed']);
        }
    };
    
    $response = $middleware->process($request, $handler);
    echo "❌ FAILED: Should have thrown UnauthorizedException\n\n";
} catch (UnauthorizedException $e) {
    echo "✅ PASSED: " . $e->getMessage() . "\n\n";
}

// Test 4: Public route (should bypass authentication)
echo "Test 4: Public route without token\n";
try {
    $request = new Request(
        method: 'POST',
        uri: '/api/auth/login',
        headers: [],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $publicRoutes = ['/api/auth/login', '/api/auth/register'];
    $middleware = new AuthenticationMiddleware($jwtService, $publicRoutes);
    $handler = new class implements RequestHandler {
        public function handle(Request $request): Response {
            return new Response(['message' => 'Public route accessed']);
        }
    };
    
    $response = $middleware->process($request, $handler);
    $body = json_decode($response->toJson(), true);
    
    if ($body['message'] === 'Public route accessed') {
        echo "✅ PASSED: Public route bypassed authentication\n\n";
    } else {
        echo "❌ FAILED: Public route did not work correctly\n\n";
    }
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 5: Public route with wildcard pattern
echo "Test 5: Public route with wildcard pattern\n";
try {
    $request = new Request(
        method: 'GET',
        uri: '/api/public/docs/swagger',
        headers: [],
        query: [],
        body: [],
        files: [],
        server: []
    );
    
    $publicRoutes = ['/api/public/*', '/api/auth/*'];
    $middleware = new AuthenticationMiddleware($jwtService, $publicRoutes);
    $handler = new class implements RequestHandler {
        public function handle(Request $request): Response {
            return new Response(['message' => 'Public wildcard route accessed']);
        }
    };
    
    $response = $middleware->process($request, $handler);
    $body = json_decode($response->toJson(), true);
    
    if ($body['message'] === 'Public wildcard route accessed') {
        echo "✅ PASSED: Wildcard public route bypassed authentication\n\n";
    } else {
        echo "❌ FAILED: Wildcard public route did not work correctly\n\n";
    }
} catch (\Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
}

echo "=== Test Summary ===\n";
echo "Generated Access Token: " . substr($tokens['access_token'], 0, 50) . "...\n";
echo "Generated Refresh Token: " . substr($tokens['refresh_token'], 0, 50) . "...\n";
echo "\nAll tests completed!\n";
