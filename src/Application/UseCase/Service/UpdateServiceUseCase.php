<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;
use Domain\Repository\ServiceMasterRepositoryInterface;

class UpdateServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,
        private ServiceMasterRepositoryInterface $serviceMasterRepo
    ) {}


    public function execute(array $servicesData): array
    {
        $updated = [];

        foreach ($servicesData as $data) {
            if (!isset($data['id'], $data['service_master_id'])) {
                continue;
            }

            $service = $this->serviceRepo->findById($data['id']);
            if (!$service) {
                continue;
            }

            $serviceMaster = $this->serviceMasterRepo->findById($data['service_master_id']);
            if (!$serviceMaster) {
                continue;
            }

            if (isset($data['task_id'])) {
                $service->setTaskId($data['task_id']);
            }

            $service->setServiceMaster($serviceMaster);
            $service->setQuantity($data['quantity'] ?? $service->getQuantity());
            $service->setUnitPrice($data['unit_price'] ?? $service->getUnitPrice());
            $service->setTotalAmount($data['total_amount'] ?? $service->getTotalAmount());

            $saved = $this->serviceRepo->save($service);
            if ($saved) {
                $updated[] = $saved;
            }
        }

        return $updated;
    }
}
