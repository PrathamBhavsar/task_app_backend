<?php

namespace Application\UseCase\Bill;

use Domain\Repository\BillRepositoryInterface;

class DeleteBillUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $bill = $this->repo->findById($id);
        if ($bill) {
            $this->repo->delete($bill);
        }
    }
}
