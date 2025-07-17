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

    public function generateToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + 86400, // 1 day expiry
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
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
}
