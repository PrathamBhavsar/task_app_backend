<?php

declare(strict_types=1);

namespace Framework\Health;

class CheckResult
{
    public function __construct(
        public readonly bool $healthy,
        public readonly ?string $message = null,
        public readonly array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->healthy ? 'healthy' : 'unhealthy',
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
