<?php

namespace Application\UseCase\ServiceMaster;

use Domain\Repository\ServiceMasterRepositoryInterface;

class DeleteServiceMasterUseCase
{
    public function __construct(private ServiceMasterRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $serviceMaster = $this->repo->findById($id);
        if ($serviceMaster) {
            $this->repo->delete($serviceMaster);
        }
    }
}
