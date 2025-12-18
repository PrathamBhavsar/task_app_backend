<?php

declare(strict_types=1);

namespace Interface\Http\DTO;

use Framework\Http\Response;

/**
 * Standardized API response builder
 * 
 * Provides consistent response formats for success, error, validation errors,
 * and collection responses.
 */
class ApiResponse
{
    /**
     * Create a successful response with data
     * 
     * @param mixed $data The response data
     * @param int $status HTTP status code (default: 200)
     * @return Response
     */
    public static function success(mixed $data, int $status = 200): Response
    {
        $body = [
            'success' => true,
            'data' => self::serializeData($data)
        ];
        
        return new Response(
            body: $body,
            status: $status,
            headers: ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create an error response
     * 
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param string|null $code Error code (optional)
     * @param string|null $errorId Unique error ID for tracking (optional)
     * @param array $details Additional error details (optional)
     * @return Response
     */
    public static function error(
        string $message,
        int $status = 400,
        ?string $code = null,
        ?string $errorId = null,
        array $details = []
    ): Response {
        $error = [
            'message' => $message
        ];
        
        if ($code !== null) {
            $error['code'] = $code;
        }
        
        if ($errorId !== null) {
            $error['error_id'] = $errorId;
        }
        
        if (!empty($details)) {
            $error['details'] = $details;
        }
        
        $body = [
            'success' => false,
            'error' => $error
        ];
        
        return new Response(
            body: $body,
            status: $status,
            headers: ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create a validation error response
     * 
     * @param array $errors Validation errors (field => messages)
     * @param string $message Main error message (default: "Validation failed")
     * @param string|null $errorId Unique error ID for tracking (optional)
     * @return Response
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed',
        ?string $errorId = null
    ): Response {
        $error = [
            'message' => $message,
            'code' => 'VALIDATION_ERROR',
            'details' => $errors
        ];
        
        if ($errorId !== null) {
            $error['error_id'] = $errorId;
        }
        
        $body = [
            'success' => false,
            'error' => $error
        ];
        
        return new Response(
            body: $body,
            status: 422,
            headers: ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create a collection response with items and optional metadata
     * 
     * @param array $items The collection items
     * @param string $key The key name for items (default: "items")
     * @param array|null $meta Optional metadata (pagination, totals, etc.)
     * @param int $status HTTP status code (default: 200)
     * @return Response
     */
    public static function collection(
        array $items,
        string $key = 'items',
        ?array $meta = null,
        int $status = 200
    ): Response {
        $data = [
            $key => array_map(fn($item) => self::serializeData($item), $items)
        ];
        
        if ($meta !== null) {
            $data['meta'] = $meta;
        }
        
        $body = [
            'success' => true,
            'data' => $data
        ];
        
        return new Response(
            body: $body,
            status: $status,
            headers: ['Content-Type' => 'application/json']
        );
    }

    /**
     * Create a not found error response
     * 
     * @param string $message Error message (default: "Resource not found")
     * @param string|null $errorId Unique error ID for tracking (optional)
     * @return Response
     */
    public static function notFound(
        string $message = 'Resource not found',
        ?string $errorId = null
    ): Response {
        return self::error(
            message: $message,
            status: 404,
            code: 'NOT_FOUND',
            errorId: $errorId
        );
    }

    /**
     * Create an unauthorized error response
     * 
     * @param string $message Error message (default: "Unauthorized")
     * @param string|null $errorId Unique error ID for tracking (optional)
     * @return Response
     */
    public static function unauthorized(
        string $message = 'Unauthorized',
        ?string $errorId = null
    ): Response {
        return self::error(
            message: $message,
            status: 401,
            code: 'UNAUTHORIZED',
            errorId: $errorId
        );
    }

    /**
     * Create a forbidden error response
     * 
     * @param string $message Error message (default: "Forbidden")
     * @param string|null $errorId Unique error ID for tracking (optional)
     * @return Response
     */
    public static function forbidden(
        string $message = 'Forbidden',
        ?string $errorId = null
    ): Response {
        return self::error(
            message: $message,
            status: 403,
            code: 'FORBIDDEN',
            errorId: $errorId
        );
    }

    /**
     * Generate a unique error ID for tracking
     * 
     * @return string
     */
    public static function generateErrorId(): string
    {
        return 'err_' . bin2hex(random_bytes(8));
    }

    /**
     * Serialize data for JSON response
     * 
     * Handles ResponseDTO objects, JsonSerializable objects, and arrays
     * 
     * @param mixed $data
     * @return mixed
     */
    private static function serializeData(mixed $data): mixed
    {
        if ($data instanceof ResponseDTO) {
            return $data->toArray();
        }
        
        if ($data instanceof \JsonSerializable) {
            return $data->jsonSerialize();
        }
        
        if (is_array($data)) {
            return array_map(fn($item) => self::serializeData($item), $data);
        }
        
        if (is_object($data)) {
            // Convert objects to arrays using public properties
            return get_object_vars($data);
        }
        
        return $data;
    }
}
