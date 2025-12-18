<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Http\FieldFilter;
use Framework\Http\InvalidFieldException;

/**
 * FieldFilterMiddleware - Apply field filtering to API responses
 * 
 * Processes the 'fields' query parameter and filters response data
 * to include only the requested fields. Supports nested field selection
 * with dot notation.
 * 
 * Usage:
 * - ?fields=id,name,email
 * - ?fields=id,name,address.city,address.country
 */
class FieldFilterMiddleware implements MiddlewareInterface
{
    private FieldFilter $fieldFilter;

    public function __construct(?FieldFilter $fieldFilter = null)
    {
        $this->fieldFilter = $fieldFilter ?? new FieldFilter();
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        // Get the fields parameter from query string
        $fieldsParam = $request->query['fields'] ?? null;
        
        // If no fields parameter, pass through without filtering
        if ($fieldsParam === null || trim($fieldsParam) === '') {
            return $handler->handle($request);
        }

        // Parse the fields parameter
        $fields = $this->fieldFilter->parseFields($fieldsParam);
        
        if (empty($fields)) {
            return $handler->handle($request);
        }

        // Store fields in request attributes for potential use by controllers
        $request = $request->withAttribute('requested_fields', $fields);

        // Get the response from the handler
        $response = $handler->handle($request);

        // Only filter successful responses with data
        if ($response->status >= 200 && $response->status < 300) {
            try {
                $filteredBody = $this->filterResponseBody($response->body, $fields);
                
                return new Response(
                    body: $filteredBody,
                    status: $response->status,
                    headers: $response->headers
                );
            } catch (InvalidFieldException $e) {
                // Return 400 error for invalid field names
                return $this->createInvalidFieldResponse($e);
            }
        }

        return $response;
    }

    /**
     * Filter response body based on requested fields
     * 
     * @param mixed $body Response body
     * @param array $fields Requested fields
     * @return mixed Filtered body
     * @throws InvalidFieldException
     */
    private function filterResponseBody(mixed $body, array $fields): mixed
    {
        if (!is_array($body)) {
            return $body;
        }

        // Handle standard API response format with 'data' key
        if (isset($body['success']) && isset($body['data'])) {
            $body['data'] = $this->fieldFilter->filter($body['data'], $fields);
            return $body;
        }

        // Handle direct data response
        return $this->fieldFilter->filter($body, $fields);
    }

    /**
     * Create error response for invalid field names
     * 
     * @param InvalidFieldException $e
     * @return Response
     */
    private function createInvalidFieldResponse(InvalidFieldException $e): Response
    {
        $error = [
            'message' => $e->getMessage(),
            'code' => 'INVALID_FIELD',
            'field' => $e->getFieldPath()
        ];

        if (!empty($e->getAvailableFields())) {
            $error['available_fields'] = $e->getAvailableFields();
        }

        $body = [
            'success' => false,
            'error' => $error
        ];

        return new Response(
            body: $body,
            status: 400,
            headers: ['Content-Type' => 'application/json']
        );
    }
}

