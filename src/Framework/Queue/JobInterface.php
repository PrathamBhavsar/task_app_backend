<?php

declare(strict_types=1);

namespace Framework\Queue;

interface JobInterface
{
    public function handle(): void;
    
    public function failed(\Throwable $exception): void;
    
    public function getMaxTries(): int;
    
    public function getRetryDelay(): int;
}
