<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;



class DeleteServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,
    ) {}

    public function execute(int $id): void
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) return;

        $taskId = $service->getTaskId();

        $this->serviceRepo->delete($service);
    }
}
