<?php

declare(strict_types=1);

namespace Framework\Validation;

class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly array $errors = []
    ) {}
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        
        $firstField = array_key_first($this->errors);
        $firstFieldErrors = $this->errors[$firstField];
        
        return is_array($firstFieldErrors) ? $firstFieldErrors[0] : $firstFieldErrors;
    }
    
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
    
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
}
