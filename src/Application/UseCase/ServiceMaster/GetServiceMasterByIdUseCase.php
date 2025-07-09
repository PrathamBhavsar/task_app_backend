<?php

namespace Application\UseCase\ServiceMaster;

use Domain\Repository\ServiceMasterRepositoryInterface;
use Domain\Entity\ServiceMaster;

class GetServiceMasterByIdUseCase
{
    public function __construct(private ServiceMasterRepositoryInterface $repo) {}

    public function execute(int $id): ?ServiceMaster
    {
        return $this->repo->findById($id);
    }
}
