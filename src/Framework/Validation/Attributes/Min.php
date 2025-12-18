<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min implements ValidationAttributeInterface
{
    public function __construct(
        public readonly int|float $value
    ) {}
    
    public function validate(mixed $value, string $fieldName): ValidationRuleResult
    {
        // Allow null (use Required attribute for mandatory fields)
        if ($value === null) {
            return ValidationRuleResult::success();
        }
        
        // Convert to numeric if it's a numeric string
        if (is_string($value) && is_numeric($value)) {
            $value = $value + 0; // Convert to int or float
        }
        
        if (!is_numeric($value)) {
            return ValidationRuleResult::fail("The {$fieldName} field must be a number");
        }
        
        if ($value < $this->value) {
            return ValidationRuleResult::fail(
                "The {$fieldName} field must be at least {$this->value}"
            );
        }
        
        return ValidationRuleResult::success();
    }
}
