<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MaxLength implements ValidationAttributeInterface
{
    public function __construct(
        public readonly int $length
    ) {}
    
    public function validate(mixed $value, string $fieldName): ValidationRuleResult
    {
        // Allow null or empty values (use Required attribute for mandatory fields)
        if ($value === null || $value === '') {
            return ValidationRuleResult::success();
        }
        
        if (!is_string($value)) {
            return ValidationRuleResult::fail("The {$fieldName} field must be a string");
        }
        
        // Use mb_strlen for proper UTF-8 character counting
        if (mb_strlen($value) > $this->length) {
            return ValidationRuleResult::fail(
                "The {$fieldName} field must not exceed {$this->length} characters"
            );
        }
        
        return ValidationRuleResult::success();
    }
}
