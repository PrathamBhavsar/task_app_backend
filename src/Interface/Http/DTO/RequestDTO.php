<?php

declare(strict_types=1);

namespace Interface\Http\DTO;

/**
 * Base class for Request DTOs
 * 
 * Provides a standard pattern for creating type-safe request DTOs
 * with validation attributes and array conversion.
 */
abstract class RequestDTO
{
    /**
     * Create a DTO instance from an array of data
     * 
     * This method should be implemented by child classes to map
     * array data to constructor parameters.
     * 
     * @param array $data The request data
     * @return static
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Convert the DTO to an array
     * 
     * @return array
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_READONLY);
        
        $data = [];
        foreach ($properties as $property) {
            $data[$property->getName()] = $property->getValue($this);
        }
        
        return $data;
    }
}
