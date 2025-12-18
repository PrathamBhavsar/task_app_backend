<?php

declare(strict_types=1);

namespace Framework\Error;

class RateLimitException extends \Exception
{
    public function __construct(
        public readonly int $retryAfter,
        string $message = 'Too many requests',
        int $code = 429,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
