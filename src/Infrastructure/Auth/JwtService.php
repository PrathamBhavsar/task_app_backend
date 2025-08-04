<?php

namespace Infrastructure\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Domain\Entity\User;
use Exception;

class JwtService
{
    private string $secret = 'your-secret-key';
    private string $algo = 'HS256';

    public function generateTokens(User $user): array
    {
        $now = time();

        $accessPayload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => $now,
            'exp' => $now + 3600 // 1 hour
        ];

        $refreshPayload = [
            'sub' => $user->getId(),
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + (7 * 24 * 60 * 60) // 7 days
        ];

        return [
            'access_token' => JWT::encode($accessPayload, $this->secret, $this->algo),
            'refresh_token' => JWT::encode($refreshPayload, $this->secret, $this->algo)
        ];
    }


    public function decodeToken(string $token): ?array
    {
        try {
            return (array) JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getUserIdFromToken(string $token): ?int
    {
        $decoded = $this->decodeToken($token);
        return $decoded['sub'] ?? null;
    }

    public function verifyToken(string $token): array
    {
        try {
            return (array) JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (\Exception $e) {
            throw new Exception("Invalid token");
        }
    }

    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'iat' => time(),
            'exp' => time() + (7 * 86400), // 7 days expiry
            'type' => 'refresh'
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }
}
