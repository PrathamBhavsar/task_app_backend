<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringType implements ValidationAttributeInterface
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
        
        return ValidationRuleResult::success();
    }
}
