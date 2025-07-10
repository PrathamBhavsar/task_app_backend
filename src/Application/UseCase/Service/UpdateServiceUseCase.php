<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;
use Domain\Entity\Service;

class UpdateServiceUseCase
{
    public function __construct(private ServiceRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Service
    {
        $service = $this->repo->findById($id);
        if (!$service) return null;

        $service->setTaskId($data['task_id']);
        $service->setServiceMaster($data['service_master']);
        $service->setQuantity($data['quantity']);
        $service->setUnitPrice($data['unit_price']);
        $service->setTotalAmount($data['total_amount']);

        return $this->repo->save($service);
    }
}
