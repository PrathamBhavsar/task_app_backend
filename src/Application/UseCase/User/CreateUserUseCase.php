<?php

namespace Application\UseCase\User;

use Domain\Entity\User;
use Domain\Repository\UserRepositoryInterface;

class CreateUserUseCase
{
    public function __construct(private UserRepositoryInterface $repo) {}

    public function execute(array $data): User
    {

        $user = new User(
            name: $data['name'],
            email: $data['email'],
            contactNo: $data['contact_no'],
            created_at: $data['created_at'],
            user_type: $data['user_type'],
            address: $data['address'],
            profile_bg_color: $data['profile_bg_color'],
        );

        return $this->repo->save($user);
    }
}
