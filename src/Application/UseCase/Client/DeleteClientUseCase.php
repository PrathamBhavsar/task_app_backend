<?php

namespace Application\UseCase\Client;

use Domain\Repository\ClientRepositoryInterface;

class DeleteClientUseCase
{
    public function __construct(private ClientRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $client = $this->repo->findById($id);
        if ($client) {
            $this->repo->delete($client);
        }
    }
}
