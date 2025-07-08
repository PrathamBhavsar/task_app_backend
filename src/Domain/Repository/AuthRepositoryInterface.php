<?php

namespace Domain\Repository;

use Domain\Entity\User;

interface AuthRepositoryInterface
{
    public function login(string $email, string $password): ?User;
    public function register(array $data): ?User;
    public function emailExists(string $email): bool;
}
