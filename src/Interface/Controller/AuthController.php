<?php

namespace Interface\Controller;

use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase,
    RefreshTokenUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\JsonResponse;
use Interface\Http\DTO\ApiResponse;
use Domain\Entity\User;

class AuthController
{
    public function __construct(
        private LoginUseCase $login,
        private RegisterUseCase $register,
        private RefreshTokenUseCase $refresh,
    ) {}

    public function login(Request $request): Response
    {
        $data = $request->body;
        $result = $this->login->execute($data['email'] ?? '', $data['password'] ?? '');

        return $result
            ? ApiResponse::success($result)
            : ApiResponse::error('Invalid credentials', 401);
    }

    public function register(Request $request): Response
    {
        $data = $request->body;
        $user = $this->register->execute($data);

        return $user
            ? ApiResponse::success($user, 201)
            : ApiResponse::error('Email already registered', 400);
    }

    public function refresh(Request $request): Response
    {
        $data = $request->body;
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return ApiResponse::error('Refresh token is required', 400);
        }

        try {
            $newTokens = $this->refresh->execute($refreshToken);
            return ApiResponse::success($newTokens);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 401);
        }
    }

    public function logout(Request $request): Response
    {
        // Implement logout logic (e.g., blacklist token)
        return ApiResponse::success(['message' => 'Logged out successfully']);
    }
}
