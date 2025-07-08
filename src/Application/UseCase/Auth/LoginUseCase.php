<?php

namespace Application\UseCase\Auth;

use Domain\Repository\AuthRepositoryInterface;
use Domain\Entity\User;

class LoginUseCase
{
    public function __construct(private AuthRepositoryInterface $repo) {}

    public function execute(string $email, string $password): ?User
    {
        return $this->repo->login($email, $password);
    }
}
