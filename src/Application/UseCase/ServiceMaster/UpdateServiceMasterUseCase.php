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

        if (isset($data['name'])) {
            $serviceMaster->setName($data['name']);
        }
        
        if (isset($data['default_rate'])) {
            $serviceMaster->setDefaultRate($data['default_rate']);
        }

        return $this->repo->save($serviceMaster);
    }
}
