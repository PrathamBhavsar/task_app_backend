<?php

declare(strict_types=1);

namespace Framework\Validation;

class ValidationRuleResult
{
    public function __construct(
        private readonly bool $valid,
        private readonly string $message = ''
    ) {}
    
    public function isValid(): bool
    {
        return $this->valid;
    }
    
    public function getMessage(): string
    {
        return $this->message;
    }
    
    public static function success(): self
    {
        return new self(true);
    }
    
    public static function fail(string $message): self
    {
        return new self(false, $message);
    }
}
