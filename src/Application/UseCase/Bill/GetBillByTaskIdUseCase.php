<?php

namespace Application\UseCase\Bill;

use Domain\Repository\BillRepositoryInterface;
use Domain\Entity\Bill;

class GetBillByTaskIdUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(int $taskId): Bill
    {
        return $this->repo->findByTaskId($taskId);
    }
}
