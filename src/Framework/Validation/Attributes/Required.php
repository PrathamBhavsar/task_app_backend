<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required implements ValidationAttributeInterface
{
    public function validate(mixed $value, string $fieldName): ValidationRuleResult
    {
        // Check for null, empty string, or empty array
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return ValidationRuleResult::fail("The {$fieldName} field is required");
        }
        
        return ValidationRuleResult::success();
    }
}
