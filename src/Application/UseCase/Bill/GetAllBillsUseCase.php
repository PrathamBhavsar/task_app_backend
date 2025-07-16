<?php

namespace Application\UseCase\Bill;

use Domain\Repository\BillRepositoryInterface;

class GetAllBillsUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
