<?php

namespace Application\UseCase\ServiceMaster;

use Domain\Repository\ServiceMasterRepositoryInterface;
use Domain\Entity\ServiceMaster;

class UpdateServiceMasterUseCase
{
    public function __construct(private ServiceMasterRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?ServiceMaster
    {
        $serviceMaster = $this->repo->findById($id);
        if (!$serviceMaster) return null;

        $serviceMaster->setName($data['name']);
        $serviceMaster->setContactNo($data['contact_no']);
        $serviceMaster->setAddress($data['address']);
        $serviceMaster->setFirmName($data['firm_name']);
        $serviceMaster->setProfileBgColor($data['profile_bg_color']);

        return $this->repo->save($serviceMaster);
    }
}
