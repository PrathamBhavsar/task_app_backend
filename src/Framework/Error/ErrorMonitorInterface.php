<?php

declare(strict_types=1);

namespace Framework\Error;

use Framework\Http\Request;

/**
 * Interface for error monitoring services
 */
interface ErrorMonitorInterface
{
    /**
     * Capture an exception with context
     *
     * @param \Throwable $exception The exception to capture
     * @param Request $request The HTTP request context
     * @param array $context Additional context data
     * @return string|null The event ID from the monitoring service
     */
    public function captureException(\Throwable $exception, Request $request, array $context = []): ?string;

    /**
     * Capture a message with context
     *
     * @param string $message The message to capture
     * @param string $level The severity level (debug, info, warning, error, fatal)
     * @param array $context Additional context data
     * @return string|null The event ID from the monitoring service
     */
    public function captureMessage(string $message, string $level = 'error', array $context = []): ?string;

    /**
     * Set user context for error tracking
     *
     * @param array $user User information (id, email, username, etc.)
     * @return void
     */
    public function setUser(array $user): void;

    /**
     * Add tags to the current context
     *
     * @param array $tags Key-value pairs of tags
     * @return void
     */
    public function setTags(array $tags): void;

    /**
     * Add extra context data
     *
     * @param string $key The context key
     * @param mixed $value The context value
     * @return void
     */
    public function setExtra(string $key, mixed $value): void;
}
