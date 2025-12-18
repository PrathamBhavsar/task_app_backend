<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Attribute;
use Framework\Validation\ValidationRuleResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Pattern implements ValidationAttributeInterface
{
    public function __construct(
        public readonly string $regex,
        public readonly string $message = ''
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
        
        // Validate against regex pattern
        if (!preg_match($this->regex, $value)) {
            $message = $this->message ?: "The {$fieldName} field format is invalid";
            return ValidationRuleResult::fail($message);
        }
        
        return ValidationRuleResult::success();
    }
}
