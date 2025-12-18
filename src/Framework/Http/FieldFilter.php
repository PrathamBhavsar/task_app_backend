<?php

declare(strict_types=1);

namespace Framework\Http;

/**
 * FieldFilter - Selective field inclusion for API responses
 * 
 * Allows clients to request specific fields from API responses using the
 * 'fields' query parameter. Supports nested field selection with dot notation.
 * 
 * Examples:
 * - ?fields=id,name,email
 * - ?fields=id,name,address.city,address.country
 * - ?fields=user.id,user.name,user.profile.avatar
 */
class FieldFilter
{
    /**
     * Parse fields query parameter into array of field paths
     * 
     * @param string|null $fieldsParam The fields query parameter value
     * @return array Array of field paths (e.g., ['id', 'name', 'address.city'])
     */
    public function parseFields(?string $fieldsParam): array
    {
        if ($fieldsParam === null || trim($fieldsParam) === '') {
            return [];
        }
        
        // Split by comma and trim whitespace
        $fields = array_map('trim', explode(',', $fieldsParam));
        
        // Remove empty values
        return array_filter($fields, fn($field) => $field !== '');
    }

    /**
     * Filter data to include only requested fields
     * 
     * @param mixed $data The data to filter (array, object, or scalar)
     * @param array $fields Array of field paths to include
     * @return mixed Filtered data
     * @throws InvalidFieldException If a requested field doesn't exist
     */
    public function filter(mixed $data, array $fields): mixed
    {
        if (empty($fields)) {
            return $data;
        }
        
        if (is_array($data)) {
            // Check if it's a list of items (numeric keys)
            if ($this->isSequentialArray($data)) {
                return array_map(fn($item) => $this->filterSingle($item, $fields), $data);
            }
            
            // Single associative array
            return $this->filterSingle($data, $fields);
        }
        
        if (is_object($data)) {
            return $this->filterSingle($data, $fields);
        }
        
        // Scalar values can't be filtered
        return $data;
    }

    /**
     * Filter a single item (array or object)
     * 
     * @param mixed $item The item to filter
     * @param array $fields Array of field paths to include
     * @return array Filtered item as array
     * @throws InvalidFieldException If a requested field doesn't exist
     */
    private function filterSingle(mixed $item, array $fields): array
    {
        // Convert object to array for uniform processing
        $data = $this->toArray($item);
        
        // Build field tree from flat field paths
        $fieldTree = $this->buildFieldTree($fields);
        
        // Apply filtering
        return $this->applyFieldTree($data, $fieldTree, $fields);
    }

    /**
     * Convert data to array representation
     * 
     * @param mixed $data
     * @return array
     */
    private function toArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        
        if ($data instanceof \JsonSerializable) {
            $serialized = $data->jsonSerialize();
            return is_array($serialized) ? $serialized : [$serialized];
        }
        
        if (is_object($data)) {
            // Try to get public properties
            $array = get_object_vars($data);
            
            // If object has toArray method, use it
            if (method_exists($data, 'toArray')) {
                return $data->toArray();
            }
            
            return $array;
        }
        
        return [$data];
    }

    /**
     * Build a tree structure from flat field paths
     * 
     * Example: ['id', 'name', 'address.city', 'address.country']
     * Becomes: ['id' => true, 'name' => true, 'address' => ['city' => true, 'country' => true]]
     * 
     * @param array $fields Array of field paths
     * @return array Field tree structure
     */
    private function buildFieldTree(array $fields): array
    {
        $tree = [];
        
        foreach ($fields as $field) {
            $parts = explode('.', $field);
            $current = &$tree;
            
            foreach ($parts as $part) {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            
            // Mark leaf nodes as true
            if (empty($current)) {
                $current = true;
            }
        }
        
        return $tree;
    }

    /**
     * Apply field tree to filter data
     * 
     * @param array $data The data to filter
     * @param array $fieldTree The field tree structure
     * @param array $originalFields Original field paths for error messages
     * @param string $path Current path for error messages
     * @return array Filtered data
     * @throws InvalidFieldException If a requested field doesn't exist
     */
    private function applyFieldTree(array $data, array $fieldTree, array $originalFields, string $path = ''): array
    {
        $result = [];
        
        foreach ($fieldTree as $field => $subTree) {
            $currentPath = $path === '' ? $field : $path . '.' . $field;
            
            // Check if field exists in data
            if (!array_key_exists($field, $data)) {
                throw new InvalidFieldException(
                    "Invalid field: '{$currentPath}'. Field does not exist in the response data.",
                    $currentPath,
                    $this->getAvailableFields($data)
                );
            }
            
            $value = $data[$field];
            
            // If subTree is true, include the entire value
            if ($subTree === true) {
                $result[$field] = $value;
                continue;
            }
            
            // If subTree is an array, recursively filter nested data
            if (is_array($subTree) && !empty($subTree)) {
                if ($value === null) {
                    $result[$field] = null;
                } elseif (is_array($value)) {
                    // Check if it's a list of items
                    if ($this->isSequentialArray($value)) {
                        $result[$field] = array_map(
                            fn($item) => $this->applyFieldTree(
                                $this->toArray($item),
                                $subTree,
                                $originalFields,
                                $currentPath
                            ),
                            $value
                        );
                    } else {
                        $result[$field] = $this->applyFieldTree($value, $subTree, $originalFields, $currentPath);
                    }
                } elseif (is_object($value)) {
                    $result[$field] = $this->applyFieldTree(
                        $this->toArray($value),
                        $subTree,
                        $originalFields,
                        $currentPath
                    );
                } else {
                    // Scalar value but nested fields requested
                    throw new InvalidFieldException(
                        "Invalid field: '{$currentPath}'. Cannot access nested fields on scalar value.",
                        $currentPath,
                        []
                    );
                }
            } else {
                $result[$field] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Check if array is sequential (list) vs associative
     * 
     * @param array $array
     * @return bool
     */
    private function isSequentialArray(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Get available fields from data for error messages
     * 
     * @param array $data
     * @return array
     */
    private function getAvailableFields(array $data): array
    {
        return array_keys($data);
    }

    /**
     * Validate field names against available fields
     * 
     * @param array $fields Requested field paths
     * @param mixed $data Sample data to validate against
     * @return array Array of invalid field paths
     */
    public function validateFields(array $fields, mixed $data): array
    {
        $invalid = [];
        $dataArray = $this->toArray($data);
        
        foreach ($fields as $field) {
            $parts = explode('.', $field);
            $current = $dataArray;
            $path = '';
            
            foreach ($parts as $part) {
                $path = $path === '' ? $part : $path . '.' . $part;
                
                if (!is_array($current) || !array_key_exists($part, $current)) {
                    $invalid[] = $path;
                    break;
                }
                
                $current = $current[$part];
                
                // If we need to go deeper but current is not array/object
                if (!is_array($current) && !is_object($current) && $path !== $field) {
                    $invalid[] = $field;
                    break;
                }
                
                if (is_object($current)) {
                    $current = $this->toArray($current);
                }
            }
        }
        
        return array_unique($invalid);
    }
}
