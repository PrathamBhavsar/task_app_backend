<?php

namespace Interface\Controller;

use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase,
    RefreshTokenUseCase
};
use Interface\Http\JsonResponse;
use Domain\Entity\User;

class AuthController
{
    public function __construct(
        private LoginUseCase $login,
        private RegisterUseCase $register,
        private RefreshTokenUseCase $refresh,
    ) {}

    public function login(array $data)
    {
        $result = $this->login->execute($data['email'], $data['password']);

        return $result
            ? JsonResponse::ok($result)
            : JsonResponse::error("Invalid credentials", 401);
    }

    public function register(array $data)
    {

        $user = $this->register->execute($data);

        return $user
            ? JsonResponse::ok($user)
            : JsonResponse::error("Email already registered", 400);
    }

    public function refreshToken(array $data)
    {
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return JsonResponse::error("Refresh token is required", 400);
        }

        try {
            $newTokens = $this->refresh->execute($refreshToken);

            return JsonResponse::ok($newTokens);
        } catch (\Exception $e) {
            return JsonResponse::error($e->getMessage(), 401);
        }
    }
}
