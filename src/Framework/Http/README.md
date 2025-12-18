# Field Filtering

The Field Filtering feature allows API clients to request only specific fields from API responses, reducing payload size and improving performance.

## Overview

Field filtering is implemented through:
- `FieldFilter` class - Core filtering logic
- `FieldFilterMiddleware` - Automatic filtering of responses
- `InvalidFieldException` - Exception for invalid field requests

## Usage

### Basic Field Selection

Request only specific fields using the `fields` query parameter:

```
GET /api/clients?fields=id,name,email
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ]
}
```

### Nested Field Selection

Use dot notation to select nested fields:

```
GET /api/clients?fields=id,name,address.city,address.country
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "address": {
        "city": "New York",
        "country": "USA"
      }
    }
  ]
}
```

### Multiple Nested Levels

```
GET /api/users?fields=id,name,profile.avatar,profile.settings.theme
```

## Middleware Integration

Add the `FieldFilterMiddleware` to your middleware stack:

```php
use Framework\Middleware\FieldFilterMiddleware;

// In your bootstrap or routing configuration
$pipeline->pipe(new FieldFilterMiddleware());
```

The middleware automatically:
1. Parses the `fields` query parameter
2. Filters the response data
3. Returns 400 error for invalid field names

## Error Handling

When an invalid field is requested, the API returns a 400 error:

```
GET /api/clients?fields=id,invalid_field
```

Response:
```json
{
  "success": false,
  "error": {
    "message": "Invalid field: 'invalid_field'. Field does not exist in the response data.",
    "code": "INVALID_FIELD",
    "field": "invalid_field",
    "available_fields": ["id", "name", "email", "contact_no", "address"]
  }
}
```

## Programmatic Usage

You can also use the `FieldFilter` class directly in your controllers:

```php
use Framework\Http\FieldFilter;

class ClientController
{
    private FieldFilter $fieldFilter;

    public function __construct()
    {
        $this->fieldFilter = new FieldFilter();
    }

    public function index(Request $request): Response
    {
        $clients = $this->clientRepository->findAll();
        
        // Parse fields from query parameter
        $fields = $this->fieldFilter->parseFields($request->query['fields'] ?? null);
        
        // Filter the data
        if (!empty($fields)) {
            $clients = $this->fieldFilter->filter($clients, $fields);
        }
        
        return ApiResponse::success($clients);
    }
}
```

## Field Validation

Validate fields before filtering:

```php
$fields = $fieldFilter->parseFields($request->query['fields'] ?? null);
$sampleData = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];

$invalidFields = $fieldFilter->validateFields($fields, $sampleData);

if (!empty($invalidFields)) {
    return ApiResponse::error(
        "Invalid fields: " . implode(', ', $invalidFields),
        400
    );
}
```

## Supported Data Types

The field filter supports:
- Arrays (associative and sequential)
- Objects (with public properties or `toArray()` method)
- Objects implementing `JsonSerializable`
- Nested structures of the above

## Performance Considerations

- Field filtering reduces response payload size
- Smaller payloads mean faster network transfer
- Less data for clients to parse
- Particularly beneficial for mobile clients on slow connections

## Best Practices

1. **Document available fields** - Provide API documentation listing available fields for each endpoint
2. **Use with pagination** - Combine field filtering with pagination for optimal performance
3. **Cache filtered responses** - Consider caching commonly requested field combinations
4. **Validate early** - Validate field names early to provide clear error messages
5. **Default fields** - Consider providing sensible defaults when no fields are specified

## Examples

### Example 1: User Profile with Nested Data

```
GET /api/users/123?fields=id,username,profile.bio,profile.avatar,stats.posts_count
```

### Example 2: Product List with Minimal Data

```
GET /api/products?fields=id,name,price,image.thumbnail
```

### Example 3: Order with Related Data

```
GET /api/orders/456?fields=id,status,total,customer.name,customer.email,items.product.name,items.quantity
```

## Integration with Other Features

### With Caching

Field filtering works seamlessly with ETag caching. Different field combinations generate different ETags.

### With Rate Limiting

Field filtering doesn't affect rate limiting - each request counts regardless of fields requested.

### With API Versioning

Field filtering is version-agnostic and works across all API versions.

## Troubleshooting

### Issue: Fields not being filtered

**Solution**: Ensure `FieldFilterMiddleware` is added to your middleware pipeline and positioned after authentication but before response is sent.

### Issue: Getting 400 errors for valid fields

**Solution**: Check that your response data structure matches the field paths. Remember that field names are case-sensitive.

### Issue: Nested fields not working

**Solution**: Ensure nested data is properly structured as arrays or objects. Scalar values cannot have nested field access.

