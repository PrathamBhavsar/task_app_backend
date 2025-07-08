<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\User;
use Domain\Repository\AuthRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;


class AuthRepository implements AuthRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function emailExists(string $email): bool
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]) !== null;
    }

    public function login(string $email, string $password): ?User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user && password_verify($password, $user->getPassword())) {
            return $user;
        }

        return null;
    }

    public function register(array $data): ?User
    {
        $user = new User(
            $data['name'],
            $data['contact_no'],
            $data['address'],
            $data['email'],
            $data['user_type'],
            $data['profile_bg_color']
        );

        $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
