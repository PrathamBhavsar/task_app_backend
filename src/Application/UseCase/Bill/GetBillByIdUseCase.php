<?php

namespace Application\UseCase\Bill;

use Domain\Repository\BillRepositoryInterface;
use Domain\Entity\Bill;

class GetBillByIdUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(int $id): ?Bill
    {
        return $this->repo->findById($id);
    }
}
