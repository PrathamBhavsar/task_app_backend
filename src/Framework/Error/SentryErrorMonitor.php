<?php

declare(strict_types=1);

namespace Framework\Error;

use Framework\Http\Request;

/**
 * Sentry integration adapter for error monitoring
 * 
 * This is a lightweight adapter that integrates with Sentry SDK
 * Install Sentry SDK: composer require sentry/sentry
 */
class SentryErrorMonitor implements ErrorMonitorInterface
{
    private bool $enabled;
    private ?string $dsn;
    private string $environment;
    private ?float $sampleRate;
    private array $tags = [];
    private array $extra = [];
    private ?array $user = null;

    public function __construct(array $config)
    {
        $this->enabled = $config['enabled'] ?? false;
        $this->dsn = $config['dsn'] ?? null;
        $this->environment = $config['environment'] ?? 'production';
        $this->sampleRate = $config['sample_rate'] ?? 1.0;

        // Initialize Sentry if enabled and DSN is provided
        if ($this->enabled && $this->dsn && function_exists('\Sentry\init')) {
            \Sentry\init([
                'dsn' => $this->dsn,
                'environment' => $this->environment,
                'sample_rate' => $this->sampleRate,
                'traces_sample_rate' => $config['traces_sample_rate'] ?? 0.1,
                'send_default_pii' => $config['send_pii'] ?? false,
                'max_breadcrumbs' => $config['max_breadcrumbs'] ?? 50,
            ]);
        }
    }

    /**
     * Capture an exception with context
     */
    public function captureException(\Throwable $exception, Request $request, array $context = []): ?string
    {
        if (!$this->enabled || !$this->dsn || !function_exists('\Sentry\captureException')) {
            return null;
        }

        // Set request context
        $this->setRequestContext($request);

        // Set user context if available
        if ($this->user) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                $scope->setUser($this->user);
            });
        }

        // Set tags
        if (!empty($this->tags)) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                foreach ($this->tags as $key => $value) {
                    $scope->setTag($key, (string) $value);
                }
            });
        }

        // Set extra context
        $allExtra = array_merge($this->extra, $context);
        if (!empty($allExtra)) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($allExtra): void {
                foreach ($allExtra as $key => $value) {
                    $scope->setExtra($key, $value);
                }
            });
        }

        // Capture the exception
        $eventId = \Sentry\captureException($exception);

        return $eventId ? (string) $eventId : null;
    }

    /**
     * Capture a message with context
     */
    public function captureMessage(string $message, string $level = 'error', array $context = []): ?string
    {
        if (!$this->enabled || !$this->dsn || !function_exists('\Sentry\captureMessage')) {
            return null;
        }

        // Map level to Sentry severity
        $severity = $this->mapLevelToSeverity($level);

        // Set extra context
        if (!empty($context)) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($context): void {
                foreach ($context as $key => $value) {
                    $scope->setExtra($key, $value);
                }
            });
        }

        // Capture the message
        $eventId = \Sentry\captureMessage($message, $severity);

        return $eventId ? (string) $eventId : null;
    }

    /**
     * Set user context for error tracking
     */
    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    /**
     * Add tags to the current context
     */
    public function setTags(array $tags): void
    {
        $this->tags = array_merge($this->tags, $tags);
    }

    /**
     * Add extra context data
     */
    public function setExtra(string $key, mixed $value): void
    {
        $this->extra[$key] = $value;
    }

    /**
     * Set request context in Sentry
     */
    private function setRequestContext(Request $request): void
    {
        if (!function_exists('\Sentry\configureScope')) {
            return;
        }

        \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($request): void {
            // Set request data
            $scope->setContext('request', [
                'method' => $request->method,
                'url' => $request->uri,
                'query_string' => http_build_query($request->query),
                'headers' => $this->sanitizeHeaders($request->headers),
                'body' => $this->sanitizeBody($request->body),
            ]);

            // Set server context
            $scope->setContext('server', [
                'server_name' => $request->server['SERVER_NAME'] ?? 'unknown',
                'server_software' => $request->server['SERVER_SOFTWARE'] ?? 'unknown',
                'php_version' => PHP_VERSION,
            ]);

            // Add route tag if available
            if ($route = $request->getAttribute('route')) {
                $scope->setTag('route', $route);
            }
        });
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];

        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders, true)) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize request body to remove sensitive information
     */
    private function sanitizeBody(array $body): array
    {
        $sanitized = [];
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'api_key', 'credit_card'];

        foreach ($body as $key => $value) {
            $lowerKey = strtolower($key);
            $isSensitive = false;

            foreach ($sensitiveFields as $sensitiveField) {
                if (str_contains($lowerKey, $sensitiveField)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Map log level to Sentry severity
     */
    private function mapLevelToSeverity(string $level): \Sentry\Severity
    {
        return match (strtolower($level)) {
            'debug' => \Sentry\Severity::debug(),
            'info' => \Sentry\Severity::info(),
            'warning', 'warn' => \Sentry\Severity::warning(),
            'error' => \Sentry\Severity::error(),
            'fatal', 'critical' => \Sentry\Severity::fatal(),
            default => \Sentry\Severity::error(),
        };
    }
}
