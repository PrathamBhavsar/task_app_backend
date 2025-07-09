<?php

namespace Interface\Controller;

use Application\UseCase\Auth\{
    LoginUseCase,
    RegisterUseCase
};
use Interface\Http\JsonResponse;

class AuthController
{
    public function __construct(
        private LoginUseCase $login,
        private RegisterUseCase $register
    ) {}

    public function login(array $data)
    {
        $user = $this->login->execute($data['email'], $data['password']);
        return $user
            ? JsonResponse::ok($user->jsonSerialize())
            : JsonResponse::error("Invalid credentials", 401);
    }

    public function register(array $data)
    {
        $user = $this->register->execute($data);
        return $user
            ? JsonResponse::ok($user->jsonSerialize())
            : JsonResponse::error("Email already registered", 400);
    }
}
