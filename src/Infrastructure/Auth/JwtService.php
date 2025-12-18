<?php

namespace Infrastructure\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Domain\Entity\User;
use Exception;

class JwtService
{
    private string $secret;
    private string $algo = 'HS256';
    private string $issuer;
    private string $audience;
    private int $expiry;
    private int $refreshExpiry;
    private ?TokenBlacklist $blacklist = null;

    public function __construct(?TokenBlacklist $blacklist = null)
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $this->issuer = $_ENV['JWT_ISSUER'] ?? 'api.example.com';
        $this->audience = $_ENV['JWT_AUDIENCE'] ?? 'api.example.com';
        $this->expiry = (int) ($_ENV['JWT_EXPIRY'] ?? 3600);
        $this->refreshExpiry = (int) ($_ENV['JWT_REFRESH_EXPIRY'] ?? 604800);
        $this->blacklist = $blacklist;
    }

    public function generateTokens(User $user): array
    {
        $now = time();

        $accessPayload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => $now,
            'exp' => $now + $this->expiry,
            'iss' => $this->issuer,
            'aud' => $this->audience
        ];

        $refreshPayload = [
            'sub' => $user->getId(),
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + $this->refreshExpiry,
            'iss' => $this->issuer,
            'aud' => $this->audience
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
            $decoded = (array) JWT::decode($token, new Key($this->secret, $this->algo));
            
            // Validate issuer
            if (isset($decoded['iss']) && $decoded['iss'] !== $this->issuer) {
                throw new Exception("Invalid token issuer");
            }
            
            // Validate audience
            if (isset($decoded['aud']) && $decoded['aud'] !== $this->audience) {
                throw new Exception("Invalid token audience");
            }
            
            // Check if token is revoked
            if ($this->blacklist && $this->blacklist->isRevoked($token)) {
                throw new Exception("Token has been revoked");
            }
            
            return $decoded;
        } catch (\Exception $e) {
            throw new Exception("Invalid token: " . $e->getMessage());
        }
    }
    
    public function revokeToken(string $token): void
    {
        if ($this->blacklist) {
            $decoded = $this->decodeToken($token);
            if ($decoded && isset($decoded['exp'])) {
                $ttl = $decoded['exp'] - time();
                if ($ttl > 0) {
                    $this->blacklist->revoke($token, $ttl);
                }
            }
        }
    }

    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'sub' => $user->getId(),
            'iat' => time(),
            'exp' => time() + $this->refreshExpiry,
            'type' => 'refresh',
            'iss' => $this->issuer,
            'aud' => $this->audience
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }
}
