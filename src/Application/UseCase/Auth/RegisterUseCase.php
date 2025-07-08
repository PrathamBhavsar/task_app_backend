<?php

namespace Application\UseCase\Auth;

use Domain\Repository\AuthRepositoryInterface;
use Domain\Entity\User;

class RegisterUseCase
{
    public function __construct(private AuthRepositoryInterface $repo) {}

    public function execute(array $data): ?User
    {
        if ($this->repo->emailExists($data['email'])) {
            return null;
        }

        return $this->repo->register($data);
    }
}
