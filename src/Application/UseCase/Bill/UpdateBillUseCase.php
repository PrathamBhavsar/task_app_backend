<?php

namespace Application\UseCase\Bill;

use Domain\Repository\BillRepositoryInterface;
use Domain\Entity\Bill;

class UpdateBillUseCase
{
    public function __construct(private BillRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Bill
    {
        $bill = $this->repo->findById($id);
        if (!$bill) return null;

        $bill->setTaskId($data['task_id']);
        $bill->setSubtotal($data['subtotal']);
        $bill->setStatus($data['status']);
        $bill->setDueDate($data['due_date']);
        $bill->setTax($data['tax']);
        $bill->setTotal($data['total']);
        $bill->setNotes($data['additional_notes']);

        return $this->repo->save($bill);
    }
}
