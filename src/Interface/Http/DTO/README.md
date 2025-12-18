# DTO Infrastructure

This directory contains the Data Transfer Object (DTO) infrastructure for the API, providing type-safe request validation and standardized response formatting.

## Overview

The DTO infrastructure consists of:

1. **RequestDTO** - Base class for request DTOs with validation attributes
2. **ResponseDTO** - Base class for response DTOs with automatic JSON serialization
3. **ApiResponse** - Standardized response builder for consistent API responses

## Request DTOs

Request DTOs provide type-safe input validation using PHP attributes.

### Creating a Request DTO

```php
use Interface\Http\DTO\RequestDTO;
use Framework\Validation\Attributes\Required;
use Framework\Validation\Attributes\Email;
use Framework\Validation\Attributes\StringType;
use Framework\Validation\Attributes\MaxLength;

class CreateUserRequest extends RequestDTO
{
    public function __construct(
        #[Required, StringType, MaxLength(255)]
        public readonly string $name,
        
        #[Required, Email]
        public readonly string $email,
        
        #[Required, StringType, MinLength(8)]
        public readonly string $password
    ) {}
    
    public static function fromArray(array $data): static
    {
        return new self(
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            password: $data['password'] ?? ''
        );
    }
}
```

### Using Request DTOs in Controllers

```php
public function store(Request $request): Response
{
    // Create DTO from request body
    $dto = CreateUserRequest::fromArray($request->body);
    
    // Validate (typically done by ValidationMiddleware)
    $validator = new Validator();
    $result = $validator->validate($dto);
    
    if (!$result->isValid) {
        return ApiResponse::validationError($result->errors);
    }
    
    // Use validated data
    $user = $this->createUserUseCase->execute($dto);
    
    return ApiResponse::success(UserResponse::fromEntity($user), 201);
}
```

## Response DTOs

Response DTOs provide automatic JSON serialization and consistent data structure.

### Creating a Response DTO

```php
use Interface\Http\DTO\ResponseDTO;

class UserResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $created_at
    ) {}
    
    public static function fromEntity(User $user): static
    {
        return new self(
            id: $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            created_at: $user->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
```

### Nested Response DTOs

Response DTOs can contain other Response DTOs, which are automatically serialized:

```php
class OrderResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly UserResponse $customer,
        public readonly array $items, // Array of OrderItemResponse
        public readonly float $total
    ) {}
}
```

## ApiResponse

The `ApiResponse` class provides standardized response formats for all API endpoints.

### Success Response

```php
// Simple success response
return ApiResponse::success($data);

// Success with custom status code
return ApiResponse::success($data, 201);
```

**Output:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe"
  }
}
```

### Error Response

```php
// Basic error
return ApiResponse::error('Something went wrong', 500);

// Error with code and error ID
return ApiResponse::error(
    message: 'Invalid input',
    status: 400,
    code: 'INVALID_INPUT',
    errorId: ApiResponse::generateErrorId()
);

// Error with additional details
return ApiResponse::error(
    message: 'Payment failed',
    status: 402,
    code: 'PAYMENT_FAILED',
    errorId: ApiResponse::generateErrorId(),
    details: ['reason' => 'Insufficient funds']
);
```

**Output:**
```json
{
  "success": false,
  "error": {
    "message": "Invalid input",
    "code": "INVALID_INPUT",
    "error_id": "err_a1b2c3d4e5f6g7h8"
  }
}
```

### Validation Error Response

```php
return ApiResponse::validationError([
    'email' => ['The email field must be a valid email address'],
    'name' => ['The name field is required']
]);

// With custom message and error ID
return ApiResponse::validationError(
    errors: $validationErrors,
    message: 'The provided data is invalid',
    errorId: ApiResponse::generateErrorId()
);
```

**Output:**
```json
{
  "success": false,
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "error_id": "err_a1b2c3d4e5f6g7h8",
    "details": {
      "email": ["The email field must be a valid email address"],
      "name": ["The name field is required"]
    }
  }
}
```

### Collection Response

```php
// Simple collection
return ApiResponse::collection($users, 'users');

// Collection with metadata
return ApiResponse::collection(
    items: $users,
    key: 'users',
    meta: [
        'total' => 100,
        'page' => 1,
        'per_page' => 20
    ]
);
```

**Output:**
```json
{
  "success": true,
  "data": {
    "users": [
      {"id": 1, "name": "John"},
      {"id": 2, "name": "Jane"}
    ],
    "meta": {
      "total": 100,
      "page": 1,
      "per_page": 20
    }
  }
}
```

### Convenience Methods

```php
// 404 Not Found
return ApiResponse::notFound('User not found');

// 401 Unauthorized
return ApiResponse::unauthorized('Invalid credentials');

// 403 Forbidden
return ApiResponse::forbidden('Access denied');
```

## Error ID Generation

Error IDs help track and debug issues in production:

```php
$errorId = ApiResponse::generateErrorId();
// Returns: "err_a1b2c3d4e5f6g7h8"

// Use in error responses
return ApiResponse::error(
    message: 'Database connection failed',
    status: 500,
    code: 'DB_ERROR',
    errorId: $errorId
);

// Log the error with the same ID
$logger->error('Database error', ['error_id' => $errorId]);
```

## Complete Controller Example

```php
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;

class UserController
{
    public function index(Request $request): Response
    {
        $users = $this->listUsersUseCase->execute();
        
        return ApiResponse::collection(
            items: array_map(
                fn($user) => UserResponse::fromEntity($user),
                $users
            ),
            key: 'users'
        );
    }
    
    public function show(Request $request, int $id): Response
    {
        try {
            $user = $this->getUserUseCase->execute($id);
            return ApiResponse::success(UserResponse::fromEntity($user));
        } catch (NotFoundException $e) {
            return ApiResponse::notFound('User not found');
        }
    }
    
    public function store(Request $request): Response
    {
        $dto = CreateUserRequest::fromArray($request->body);
        
        $validator = new Validator();
        $result = $validator->validate($dto);
        
        if (!$result->isValid) {
            return ApiResponse::validationError($result->errors);
        }
        
        try {
            $user = $this->createUserUseCase->execute($dto);
            return ApiResponse::success(
                UserResponse::fromEntity($user),
                201
            );
        } catch (\Exception $e) {
            $errorId = ApiResponse::generateErrorId();
            $this->logger->error('Failed to create user', [
                'error_id' => $errorId,
                'exception' => $e
            ]);
            
            return ApiResponse::error(
                message: 'Failed to create user',
                status: 500,
                code: 'CREATE_FAILED',
                errorId: $errorId
            );
        }
    }
}
```

## Benefits

1. **Type Safety** - Request DTOs provide compile-time type checking
2. **Validation** - Declarative validation using PHP attributes
3. **Consistency** - Standardized response formats across all endpoints
4. **Error Tracking** - Unique error IDs for debugging production issues
5. **Documentation** - DTOs serve as API documentation
6. **Testability** - Easy to test with type-safe objects
7. **Maintainability** - Changes to API structure are centralized

## Migration from JsonResponse

The old `JsonResponse` class can be gradually replaced:

**Before:**
```php
JsonResponse::ok($data);
JsonResponse::error('Not found', 404);
JsonResponse::list($items, 'users');
```

**After:**
```php
ApiResponse::success($data);
ApiResponse::error('Not found', 404, 'NOT_FOUND');
ApiResponse::collection($items, 'users');
```

The new `ApiResponse` provides:
- Error IDs for tracking
- Error codes for client handling
- Consistent success/error structure
- Better type safety with Response objects
