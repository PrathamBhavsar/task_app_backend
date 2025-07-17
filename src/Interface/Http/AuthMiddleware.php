<?php

namespace Interface\Http;

use Infrastructure\Auth\JwtService;

class AuthMiddleware
{
    public function __construct(private JwtService $jwtService) {}

    public function getAuthenticatedUserId(): ?int
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) return null;

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $payload = $this->jwtService->decodeToken($token);

        return $payload['sub'] ?? null;
    }
}
