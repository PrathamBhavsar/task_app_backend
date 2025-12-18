<?php

declare(strict_types=1);

namespace Framework\Error;

use Framework\Http\Request;
use Framework\Http\Response;

class ErrorHandler
{
    private bool $debug;
    private string $logPath;
    private ErrorMonitorInterface $errorMonitor;
    private array $reportLevels;
    private string $environment;

    public function __construct(
        bool $debug = false,
        string $logPath = '',
        ?ErrorMonitorInterface $errorMonitor = null,
        array $reportLevels = [],
        string $environment = 'production'
    ) {
        $this->debug = $debug;
        $this->logPath = $logPath ?: __DIR__ . '/../../error_log';
        $this->errorMonitor = $errorMonitor ?? new NullErrorMonitor();
        $this->reportLevels = $reportLevels;
        $this->environment = $environment;
    }

    /**
     * Handle an exception and return a formatted response
     */
    public function handle(\Throwable $e, Request $request): Response
    {
        // Report the exception
        $this->report($e, $request);

        // Generate error ID for tracking
        $errorId = $this->generateErrorId();

        // Determine status code
        $statusCode = $this->getStatusCode($e);

        // Build error response
        $errorResponse = [
            'success' => false,
            'error' => [
                'message' => $e->getMessage(),
                'code' => $this->getErrorCode($e),
                'error_id' => $errorId,
            ]
        ];

        // Add validation errors if ValidationException
        if ($e instanceof ValidationException) {
            $errorResponse['error']['details'] = $e->getErrors();
        }

        // Add retry-after header for rate limit exceptions
        $headers = [];
        if ($e instanceof RateLimitException) {
            $headers['Retry-After'] = (string) $e->getRetryAfter();
        }

        // Add debug information in development mode
        if ($this->debug) {
            $errorResponse['error']['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $this->formatStackTrace($e),
            ];
        }

        return new Response(
            body: $errorResponse,
            status: $statusCode,
            headers: $headers
        );
    }

    /**
     * Report an exception to logs and monitoring service
     */
    public function report(\Throwable $e, Request $request): void
    {
        $logEntry = $this->formatLogEntry($e, $request);
        
        // Write to error log
        error_log($logEntry, 3, $this->logPath);
        
        // Report to error monitoring service if enabled and exception should be reported
        if ($this->shouldReport($e)) {
            $this->reportToMonitoring($e, $request);
        }
    }
    
    /**
     * Determine if the exception should be reported to monitoring service
     */
    private function shouldReport(\Throwable $e): bool
    {
        // Get report levels for current environment
        $levels = $this->reportLevels[$this->environment] ?? $this->reportLevels['production'] ?? [];
        
        // Check if exceptions should be reported
        if (!($levels['report_exceptions'] ?? true)) {
            return false;
        }
        
        // Check if this exception type should be ignored
        $ignoreExceptions = $levels['ignore_exceptions'] ?? [];
        foreach ($ignoreExceptions as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Report exception to monitoring service
     */
    private function reportToMonitoring(\Throwable $e, Request $request): void
    {
        try {
            // Set user context if available
            if ($user = $request->getAttribute('user')) {
                $this->errorMonitor->setUser([
                    'id' => $user['id'] ?? null,
                    'email' => $user['email'] ?? null,
                    'username' => $user['username'] ?? null,
                ]);
            }
            
            // Set tags
            $this->errorMonitor->setTags([
                'exception_type' => get_class($e),
                'http_method' => $request->method,
                'http_status' => $this->getStatusCode($e),
            ]);
            
            // Set extra context
            $this->errorMonitor->setExtra('request_uri', $request->uri);
            $this->errorMonitor->setExtra('request_method', $request->method);
            $this->errorMonitor->setExtra('query_params', $request->query);
            
            // Add route if available
            if ($route = $request->getAttribute('route')) {
                $this->errorMonitor->setExtra('route', $route);
            }
            
            // Capture the exception
            $this->errorMonitor->captureException($e, $request, [
                'environment' => $this->environment,
                'debug_mode' => $this->debug,
            ]);
        } catch (\Throwable $monitoringException) {
            // Don't let monitoring failures break the application
            // Just log the monitoring error
            error_log(
                "Error monitoring failed: " . $monitoringException->getMessage(),
                3,
                $this->logPath
            );
        }
    }

    /**
     * Generate a unique error ID for tracking
     */
    private function generateErrorId(): string
    {
        return 'err_' . bin2hex(random_bytes(8));
    }

    /**
     * Get HTTP status code from exception
     */
    private function getStatusCode(\Throwable $e): int
    {
        // Use exception code if it's a valid HTTP status code
        $code = $e->getCode();
        if ($code >= 400 && $code < 600) {
            return $code;
        }

        // Map exception types to status codes
        return match (true) {
            $e instanceof NotFoundException => 404,
            $e instanceof UnauthorizedException => 401,
            $e instanceof ForbiddenException => 403,
            $e instanceof ValidationException => 422,
            $e instanceof RateLimitException => 429,
            default => 500,
        };
    }

    /**
     * Get error code string from exception
     */
    private function getErrorCode(\Throwable $e): string
    {
        return match (true) {
            $e instanceof NotFoundException => 'NOT_FOUND',
            $e instanceof UnauthorizedException => 'UNAUTHORIZED',
            $e instanceof ForbiddenException => 'FORBIDDEN',
            $e instanceof ValidationException => 'VALIDATION_ERROR',
            $e instanceof RateLimitException => 'RATE_LIMIT_EXCEEDED',
            $e instanceof ServerException => 'SERVER_ERROR',
            default => 'INTERNAL_ERROR',
        };
    }

    /**
     * Format stack trace for debug output
     */
    private function formatStackTrace(\Throwable $e): array
    {
        $trace = [];
        
        foreach ($e->getTrace() as $index => $frame) {
            $trace[] = [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? ''),
            ];
        }
        
        return $trace;
    }

    /**
     * Format log entry with exception details
     */
    private function formatLogEntry(\Throwable $e, Request $request): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $exceptionClass = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $method = $request->method;
        $uri = $request->uri;
        
        $logEntry = "[{$timestamp}] {$exceptionClass}: {$message}\n";
        $logEntry .= "Request: {$method} {$uri}\n";
        $logEntry .= "File: {$file}:{$line}\n";
        $logEntry .= "Stack trace:\n";
        $logEntry .= $e->getTraceAsString() . "\n";
        
        // Add previous exception if exists
        if ($previous = $e->getPrevious()) {
            $logEntry .= "\nPrevious exception: " . get_class($previous) . ": " . $previous->getMessage() . "\n";
            $logEntry .= "File: " . $previous->getFile() . ":" . $previous->getLine() . "\n";
        }
        
        $logEntry .= str_repeat('-', 80) . "\n";
        
        return $logEntry;
    }
}
