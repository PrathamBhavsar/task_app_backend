<?php

namespace Application\UseCase\Service;

use Domain\Entity\Service;
use Domain\Repository\ServiceRepositoryInterface;

class CreateServiceUseCase
{
    public function __construct(private ServiceRepositoryInterface $repo) {}

    public function execute(array $data): Service
    {

        $service = new Service(
            taskId: $data['task_id'],
            serviceMaster: $data['service_master'],
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            totalAmount: $data['total_amount']
        );

        return $this->repo->save($service);
    }
}
