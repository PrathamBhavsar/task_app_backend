<?php

declare(strict_types=1);

namespace Framework\Error;

/**
 * Factory for creating error monitor instances based on configuration
 */
class ErrorMonitorFactory
{
    /**
     * Create an error monitor instance based on configuration
     *
     * @param array $config Monitoring configuration
     * @return ErrorMonitorInterface
     */
    public static function create(array $config): ErrorMonitorInterface
    {
        // If monitoring is disabled, return null monitor
        if (!($config['enabled'] ?? false)) {
            return new NullErrorMonitor();
        }

        // Get the service type
        $service = $config['service'] ?? 'sentry';

        // Create the appropriate monitor based on service type
        return match ($service) {
            'sentry' => new SentryErrorMonitor($config['sentry'] ?? []),
            // Add other monitoring services here as needed
            // 'bugsnag' => new BugsnagErrorMonitor($config['bugsnag'] ?? []),
            // 'raygun' => new RaygunErrorMonitor($config['raygun'] ?? []),
            default => new NullErrorMonitor(),
        };
    }
}
