<?php

declare(strict_types=1);

namespace Framework\Http;

class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $headers,
        public readonly array $query,
        public readonly array $body,
        public readonly array $files,
        public readonly array $server,
        public readonly array $attributes = []
    ) {}

    public static function fromGlobals(): self
    {
        return new self(
            method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
            uri: $_SERVER['REQUEST_URI'] ?? '/',
            headers: self::extractHeaders(),
            query: $_GET,
            body: self::extractBody(),
            files: $_FILES,
            server: $_SERVER,
            attributes: []
        );
    }

    private static function extractHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        
        // Add Content-Type if present
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        
        return $headers;
    }

    private static function extractBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (str_contains($contentType, 'application/json')) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }
        
        return $_POST;
    }

    public function getHeader(string $name): ?string
    {
        $name = strtoupper(str_replace('-', '_', $name));
        
        foreach ($this->headers as $key => $value) {
            if (strtoupper(str_replace('-', '_', $key)) === $name) {
                return $value;
            }
        }
        
        return null;
    }

    public function hasHeader(string $name): bool
    {
        return $this->getHeader($name) !== null;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function withAttribute(string $name, mixed $value): self
    {
        $attributes = $this->attributes;
        $attributes[$name] = $value;
        
        return new self(
            method: $this->method,
            uri: $this->uri,
            headers: $this->headers,
            query: $this->query,
            body: $this->body,
            files: $this->files,
            server: $this->server,
            attributes: $attributes
        );
    }

    public function getPath(): string
    {
        return parse_url($this->uri, PHP_URL_PATH) ?? '/';
    }

    public function withPath(string $path): self
    {
        // Preserve query string if present
        $queryString = parse_url($this->uri, PHP_URL_QUERY);
        $newUri = $path;
        
        if ($queryString !== null) {
            $newUri .= '?' . $queryString;
        }
        
        return new self(
            method: $this->method,
            uri: $newUri,
            headers: $this->headers,
            query: $this->query,
            body: $this->body,
            files: $this->files,
            server: $this->server,
            attributes: $this->attributes
        );
    }
}
