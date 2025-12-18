<?php

declare(strict_types=1);

namespace Framework\RateLimit;

class RateLimitException extends \Exception
{
    public function __construct(
        public readonly int $retryAfter,
        string $message = 'Too Many Requests'
    ) {
        parent::__construct($message, 429);
    }
}
