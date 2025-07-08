<?php

namespace Application\UseCase\User;

use Domain\Repository\UserRepositoryInterface;
use Domain\Entity\User;

class UpdateUserUseCase
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?User
    {
        $user = $this->repo->findById($id);
        if (!$user) return null;

        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setContactNo($data['contact_no']);
        $user->setUserType($data['user_type']);
        $user->setAddress($data['address']);
        $user->setProfileBgColor($data['profile_bg_color']);

        return $this->repo->save($user);
    }
}
