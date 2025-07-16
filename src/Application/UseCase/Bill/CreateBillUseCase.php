<?php

namespace Application\UseCase\Bill;

use Domain\Entity\Bill;
use Domain\Repository\BillRepositoryInterface;
use Domain\Repository\ServiceRepositoryInterface;

class CreateBillUseCase
{
    public function __construct(
        private BillRepositoryInterface $billRepo,
        private ServiceRepositoryInterface $serviceRepo
    ) {}

    public function execute(array $data): Bill
    {
        $taskId = $data['task_id'];
        $services = $this->serviceRepo->findAllByTaskId($taskId);

        if (empty($services)) {
            throw new \RuntimeException("No services found for task ID $taskId");
        }

        $subtotal = array_reduce($services, fn($carry, $service) => $carry + $service->getTotalAmount(), 0.0);
        $tax = round($subtotal * 0.07, 2);
        $total = $subtotal + $tax;

        $bill = new Bill(
            subtotal: $subtotal,
            status: $data['status'] ?? 'Pending',
            dueDate: $data['due_date'],
            tax: $tax,
            total: $total,
            additionalNotes: $data['additional_notes'] ?? null,
            taskId: $taskId
        );

        return $this->billRepo->save($bill);
    }
}
