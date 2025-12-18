<?php

declare(strict_types=1);

namespace Framework\Validation\Attributes;

use Framework\Validation\ValidationRuleResult;

interface ValidationAttributeInterface
{
    public function validate(mixed $value, string $fieldName): ValidationRuleResult;
}
