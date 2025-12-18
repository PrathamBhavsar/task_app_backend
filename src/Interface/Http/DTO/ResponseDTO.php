<?php

declare(strict_types=1);

namespace Interface\Http\DTO;

/**
 * Base class for Response DTOs
 * 
 * Provides automatic JSON serialization for response objects.
 */
abstract class ResponseDTO implements \JsonSerializable
{
    /**
     * Convert the DTO to an array for JSON serialization
     * 
     * @return array
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_READONLY);
        
        $data = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);
            
            // Handle nested DTOs
            if ($value instanceof ResponseDTO) {
                $data[$property->getName()] = $value->toArray();
            } elseif (is_array($value)) {
                $data[$property->getName()] = $this->serializeArray($value);
            } else {
                $data[$property->getName()] = $value;
            }
        }
        
        return $data;
    }

    /**
     * Serialize array values, handling nested DTOs
     * 
     * @param array $array
     * @return array
     */
    private function serializeArray(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if ($value instanceof ResponseDTO) {
                $result[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $result[$key] = $this->serializeArray($value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Specify data which should be serialized to JSON
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
