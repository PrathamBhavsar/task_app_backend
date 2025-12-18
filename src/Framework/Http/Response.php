<?php

declare(strict_types=1);

namespace Framework\Http;

class Response
{
    public function __construct(
        public readonly mixed $body,
        public readonly int $status = 200,
        public readonly array $headers = []
    ) {}

    public function withHeader(string $name, string $value): self
    {
        $headers = $this->headers;
        $headers[$name] = $value;
        
        return new self(
            body: $this->body,
            status: $this->status,
            headers: $headers
        );
    }

    public function withStatus(int $status): self
    {
        return new self(
            body: $this->body,
            status: $status,
            headers: $this->headers
        );
    }

    public function toJson(): string
    {
        if (is_string($this->body)) {
            return $this->body;
        }
        
        return json_encode($this->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function send(): void
    {
        // Set HTTP status code
        http_response_code($this->status);
        
        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Set content type if not already set
        if (!isset($this->headers['Content-Type'])) {
            header('Content-Type: application/json');
        }
        
        // Output body
        echo $this->toJson();
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->headers[$name]);
    }

    public function withBody(mixed $body): self
    {
        return new self(
            body: $body,
            status: $this->status,
            headers: $this->headers
        );
    }
}
