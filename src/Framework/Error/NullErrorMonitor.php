<?php

declare(strict_types=1);

namespace Framework\Error;

use Framework\Http\Request;

/**
 * Null implementation of ErrorMonitor for when monitoring is disabled
 */
class NullErrorMonitor implements ErrorMonitorInterface
{
    /**
     * Capture an exception (no-op)
     */
    public function captureException(\Throwable $exception, Request $request, array $context = []): ?string
    {
        return null;
    }

    /**
     * Capture a message (no-op)
     */
    public function captureMessage(string $message, string $level = 'error', array $context = []): ?string
    {
        return null;
    }

    /**
     * Set user context (no-op)
     */
    public function setUser(array $user): void
    {
        // No-op
    }

    /**
     * Add tags (no-op)
     */
    public function setTags(array $tags): void
    {
        // No-op
    }

    /**
     * Add extra context (no-op)
     */
    public function setExtra(string $key, mixed $value): void
    {
        // No-op
    }
}
