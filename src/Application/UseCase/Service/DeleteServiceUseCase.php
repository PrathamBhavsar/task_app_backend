<?php

namespace Application\UseCase\Service;

use DateTime;
use Domain\Repository\ServiceRepositoryInterface;
use Domain\Repository\QuoteRepositoryInterface;
use Domain\Repository\BillRepositoryInterface;
use Domain\Entity\Quote;
use Domain\Entity\Bill;

class DeleteServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,
        private QuoteRepositoryInterface $quoteRepo,
        private BillRepositoryInterface $billRepo
    ) {}

    public function execute(int $id): void
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) return;

        $taskId = $service->getTaskId();

        $this->serviceRepo->delete($service);
    }
}
