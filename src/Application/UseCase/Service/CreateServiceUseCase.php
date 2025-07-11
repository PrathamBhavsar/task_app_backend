<?php

namespace Application\UseCase\Service;

use Domain\Entity\Service;
use Domain\Entity\Quote;
use Domain\Repository\ServiceRepositoryInterface;
use Domain\Repository\QuoteRepositoryInterface;
use Domain\Repository\ServiceMasterRepositoryInterface;

class CreateServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,
        private QuoteRepositoryInterface $quoteRepo,
        private ServiceMasterRepositoryInterface $serviceMasterRepo
    ) {}

    public function execute(array $data): Service
    {
        $serviceMaster = $this->serviceMasterRepo->findById($data['service_master_id']);
        if (!$serviceMaster) {
            throw new \InvalidArgumentException("Invalid service_master_id");
        }

        $service = new Service(
            taskId: $data['task_id'],
            serviceMaster: $serviceMaster,
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            totalAmount: $data['total_amount']
        );

        $savedService = $this->serviceRepo->save($service);

        $this->recalculateQuote($data['task_id']);

        return $savedService;
    }

    private function recalculateQuote(int $taskId): void
    {
        $services = $this->serviceRepo->findAllByTaskId($taskId);

        $subtotal = array_reduce($services, fn($carry, $s) => $carry + $s->getTotalAmount(), 0.0);
        $tax = round($subtotal * 0.07, 2);
        $total = $subtotal + $tax;

        $quote = $this->quoteRepo->findByTaskId($taskId);

        if ($quote) {
            $quote->setSubtotal($subtotal);
            $quote->setTax($tax);
            $quote->setTotal($total);
        } else {
            $quote = new Quote(
                subtotal: $subtotal,
                tax: $tax,
                total: $total,
                notes: null,
                taskId: $taskId
            );
        }

        $this->quoteRepo->save($quote);
    }
}
