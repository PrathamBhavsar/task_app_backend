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
            contactNo: $data['contact_no'],
            address: $data['address'],
            firmName: $data['firm_name'],
            profileBgColor: $data['profile_bg_color']
        );

        return $this->repo->save($serviceMaster);
    }
}
