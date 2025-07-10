<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;

class GetAllServicesUseCase
{
    public function __construct(private ServiceRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
