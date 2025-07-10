<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;
use Domain\Entity\Service;

class GetServiceByIdUseCase
{
    public function __construct(private ServiceRepositoryInterface $repo) {}

    public function execute(int $id): ?Service
    {
        return $this->repo->findById($id);
    }
}
