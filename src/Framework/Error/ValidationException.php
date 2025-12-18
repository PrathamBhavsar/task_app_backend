<?php

declare(strict_types=1);

namespace Framework\Error;

class ValidationException extends \Exception
{
    public function __construct(
        public readonly array $errors,
        string $message = 'Validation failed',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
