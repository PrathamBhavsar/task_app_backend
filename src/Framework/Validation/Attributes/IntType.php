<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntType implements ValidationAttributeInterface
{
    public function validate(mixed $value, string $fieldName): ValidationRuleResult
    {
        // Allow null (use Required attribute for mandatory fields)
        if ($value === null) {
            return ValidationRuleResult::success();
        }
        
        // Check if it's an integer or a numeric string that represents an integer
        if (!is_int($value) && !$this->isIntegerString($value)) {
            return ValidationRuleResult::fail("The {$fieldName} field must be an integer");
        }
        
        return ValidationRuleResult::success();
    }
    
    private function isIntegerString(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        
        // Check if string represents an integer
        return preg_match('/^-?\d+$/', $value) === 1;
    }
}
