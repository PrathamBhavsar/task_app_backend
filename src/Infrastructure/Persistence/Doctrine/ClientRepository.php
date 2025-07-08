<?php

namespace Infrastructure\Persistence\Doctrine;

use Domain\Entity\Client;
use Domain\Repository\ClientRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findAll(): array
    {
        return $this->em->getRepository(Client::class)->findAll();
    }

    public function findById(int $id): ?Client
    {
        return $this->em->getRepository(Client::class)->find($id);
    }

    public function save(Client $client): Client
    {
        $this->em->persist($client);
        $this->em->flush();
        return $client;
    }

    public function delete(Client $client): void
    {
        $this->em->remove($client);
        $this->em->flush();
    }
}
