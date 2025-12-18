<?php

declare(strict_types=1);

namespace Framework\Config;

class Config
{
    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function getString(string $key, ?string $default = null): string
    {
        $value = $this->get($key, $default);
        
        if ($value === null) {
            throw new \RuntimeException("Configuration key '{$key}' is required but not set");
        }
        
        return (string) $value;
    }

    public function getInt(string $key, ?int $default = null): int
    {
        $value = $this->get($key, $default);
        
        if ($value === null) {
            throw new \RuntimeException("Configuration key '{$key}' is required but not set");
        }
        
        return (int) $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key, $default);
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }
        
        return (bool) $value;
    }

    public function getArray(string $key, array $default = []): array
    {
        $value = $this->get($key, $default);
        
        if (!is_array($value)) {
            return $default;
        }
        
        return $value;
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    public function all(): array
    {
        return $this->config;
    }
}
