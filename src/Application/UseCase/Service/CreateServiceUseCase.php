<?php

namespace Application\UseCase\Service;

use Domain\Entity\Service;

use Domain\Repository\ServiceRepositoryInterface;
use Domain\Repository\ServiceMasterRepositoryInterface;

class CreateServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,

        private ServiceMasterRepositoryInterface $serviceMasterRepo
    ) {}

    public function execute(array $data): Service
    {
        $serviceMaster = $this->serviceMasterRepo->findById($data['service_master_id']);
        if (!$serviceMaster) {
            throw new \InvalidArgumentException("Invalid service_master_id");
        }

        $service = new Service(
            taskId: $data['task_id'],
            serviceMaster: $serviceMaster,
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            totalAmount: $data['total_amount']
        );

        $savedService = $this->serviceRepo->save($service);

        return $savedService;
    }
}
