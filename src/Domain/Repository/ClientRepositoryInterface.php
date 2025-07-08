<?php

namespace Domain\Repository;

use Domain\Entity\Client;

interface ClientRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Client;
    public function save(Client $client): Client;
    public function delete(Client $client): void;
}
