<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email implements ValidationAttributeInterface
{
    public function validate(mixed $value, string $fieldName): ValidationRuleResult
    {
        // Allow null or empty values (use Required attribute for mandatory fields)
        if ($value === null || $value === '') {
            return ValidationRuleResult::success();
        }
        
        if (!is_string($value)) {
            return ValidationRuleResult::fail("The {$fieldName} field must be a string");
        }
        
        // Validate email format
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ValidationRuleResult::fail("The {$fieldName} field must be a valid email address");
        }
        
        return ValidationRuleResult::success();
    }
}
