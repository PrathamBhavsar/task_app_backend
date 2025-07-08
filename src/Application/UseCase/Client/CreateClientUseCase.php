<?php

namespace Application\UseCase\Client;

use Domain\Entity\Client;
use Domain\Repository\ClientRepositoryInterface;

class CreateClientUseCase
{
    public function __construct(private ClientRepositoryInterface $repo) {}

    public function execute(array $data): Client
    {

        $client = new Client(
            name: $data['name'],
            contactNo: $data['contact_no'],
            address: $data['address'],
            email: $data['email'],
        );

        return $this->repo->save($client);
    }
}
