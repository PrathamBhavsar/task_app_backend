<?php

namespace Application\UseCase\ServiceMaster;

use Domain\Entity\ServiceMaster;
use Domain\Repository\ServiceMasterRepositoryInterface;

class CreateServiceMasterUseCase
{
    public function __construct(private ServiceMasterRepositoryInterface $repo) {}

    public function execute(array $data): ServiceMaster
    {
        $serviceMaster = new ServiceMaster(
            name: $data['name'],
            defaultRate: $data['default_rate']
        );

        return $this->repo->save($serviceMaster);
    }
}
