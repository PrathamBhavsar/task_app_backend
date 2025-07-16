<?php

namespace Application\UseCase\Bill;

use Domain\Entity\Bill;
use Domain\Repository\BillRepositoryInterface;

class CreateBillUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(array $data): Bill
    {

        $bill = new Bill(
            subtotal: $data['subtotal'],
            status: $data['status'],
            dueDate: $data['due_date'],
            tax: $data['tax'],
            total: $data['total'],
            additionalNotes: $data['additional_notes'],
            taskId: $data['task_id'],
        );

        return $this->repo->save($bill);
    }
}
