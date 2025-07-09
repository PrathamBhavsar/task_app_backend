<?php

namespace Application\UseCase\ServiceMaster;

use Domain\Repository\ServiceMasterRepositoryInterface;

class GetAllServiceMastersUseCase
{
    public function __construct(private ServiceMasterRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
