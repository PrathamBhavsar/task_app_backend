<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;
use Domain\Entity\Service;

use Domain\Repository\ServiceMasterRepositoryInterface;

class UpdateServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,

        private ServiceMasterRepositoryInterface $serviceMasterRepo
    ) {}

    public function execute(int $id, array $data): ?Service
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) return null;

        $serviceMaster = $this->serviceMasterRepo->findById($data['service_master_id']);
        if (!$serviceMaster) throw new \InvalidArgumentException("Invalid service_master_id");

        $service->setTaskId($data['task_id']);
        $service->setServiceMaster($serviceMaster);
        $service->setQuantity($data['quantity']);
        $service->setUnitPrice($data['unit_price']);
        $service->setTotalAmount($data['total_amount']);

        $saved = $this->serviceRepo->save($service);

        return $saved;
    }
}
