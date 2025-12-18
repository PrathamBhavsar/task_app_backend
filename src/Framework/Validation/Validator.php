<?php

declare(strict_types=1);

namespace Framework\Validation;

use ReflectionClass;
use ReflectionProperty;
use Framework\Validation\Attributes\ValidationAttributeInterface;

class Validator
{
    public function validate(object $dto): ValidationResult
    {
        $errors = [];
        $reflection = new ReflectionClass($dto);
        
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyValue = $property->getValue($dto);
            
            // Get all validation attributes for this property
            $attributes = $property->getAttributes();
            
            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();
                
                // Check if it's a validation attribute
                if ($attributeInstance instanceof ValidationAttributeInterface) {
                    $result = $attributeInstance->validate($propertyValue, $propertyName);
                    
                    if (!$result->isValid()) {
                        if (!isset($errors[$propertyName])) {
                            $errors[$propertyName] = [];
                        }
                        $errors[$propertyName][] = $result->getMessage();
                    }
                }
            }
        }
        
        return new ValidationResult(
            isValid: empty($errors),
            errors: $errors
        );
    }
    
    /**
     * Sanitize input data to prevent injection attacks
     */
    public function sanitize(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                // Remove null bytes
                $value = str_replace("\0", '', $value);
                
                // Trim whitespace
                $value = trim($value);
                
                // Remove control characters except newlines and tabs
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
                
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
