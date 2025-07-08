<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\User;
use Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(User::class)->findAll();
    }

    public function findById(int $id): ?User
    {
        return $this->em->getRepository(User::class)->find($id);
    }

    public function save(User $user): User
    {
        $this->em->persist($user);
        $this->em->flush();
        return $user;
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
