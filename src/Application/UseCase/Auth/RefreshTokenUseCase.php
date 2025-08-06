<?php

namespace Application\UseCase\Auth;

use Infrastructure\Auth\JwtService;
use Domain\Repository\UserRepositoryInterface;

class RefreshTokenUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepo,
        private JwtService $jwtService
    ) {}

    public function execute(string $refreshToken): ?array
    {
        $decoded = $this->jwtService->decodeToken($refreshToken);

        if (!$decoded || ($decoded['type'] ?? '') !== 'refresh') return null;

        $user = $this->userRepo->findById($decoded['sub']);
        if (!$user) return null;

        return $this->jwtService->generateTokens($user);
    }
}
