<?php

namespace Application\UseCase\Client;

use Domain\Repository\ClientRepositoryInterface;
use Domain\Entity\Client;

class GetClientByIdUseCase
{
    public function __construct(private ClientRepositoryInterface $repo) {}

    public function execute(int $id): ?Client
    {
        return $this->repo->findById($id);
    }
}
