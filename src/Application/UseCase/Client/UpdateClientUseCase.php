<?php

namespace Application\UseCase\Client;

use Domain\Repository\ClientRepositoryInterface;
use Domain\Entity\Client;

class UpdateClientUseCase
{
    public function __construct(private ClientRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Client
    {
        $client = $this->repo->findById($id);
        if (!$client) return null;

        $client->setName($data['name']);
        $client->setContactNo($data['contact_no']);
        $client->setAddress($data['address']);
        $client->setEmail($data['email']);

        return $this->repo->save($client);
    }
}
