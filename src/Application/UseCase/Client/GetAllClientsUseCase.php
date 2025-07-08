<?php

namespace Application\UseCase\Client;

use Domain\Repository\ClientRepositoryInterface;

class GetAllClientsUseCase
{
    public function __construct(private ClientRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
