<?php

declare(strict_types=1);

namespace Framework\Http;

/**
 * Exception thrown when invalid fields are requested in field filtering
 */
class InvalidFieldException extends \Exception
{
    public function __construct(
        string $message,
        public readonly string $fieldPath,
        public readonly array $availableFields = [],
        int $code = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getFieldPath(): string
    {
        return $this->fieldPath;
    }

    public function getAvailableFields(): array
    {
        return $this->availableFields;
    }
}

