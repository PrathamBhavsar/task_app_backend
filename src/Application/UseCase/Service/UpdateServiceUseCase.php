<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;
use Domain\Entity\Service;
use Domain\Entity\Quote;
use Domain\Repository\ServiceMasterRepositoryInterface;
use Domain\Repository\QuoteRepositoryInterface;

class UpdateServiceUseCase
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepo,
        private QuoteRepositoryInterface $quoteRepo,
        private ServiceMasterRepositoryInterface $serviceMasterRepo
    ) {}

    public function execute(int $id, array $data): ?Service
    {
        $service = $this->serviceRepo->findById($id);
        if (!$service) return null;

        $serviceMaster = $this->serviceMasterRepo->findById($data['service_master_id']);
        if (!$serviceMaster) throw new \InvalidArgumentException("Invalid service_master_id");

        $service->setTaskId($data['task_id']);
        $service->setServiceMaster($serviceMaster);
        $service->setQuantity($data['quantity']);
        $service->setUnitPrice($data['unit_price']);
        $service->setTotalAmount($data['total_amount']);

        $saved = $this->serviceRepo->save($service);
        $this->recalculateQuote($data['task_id']);

        return $saved;
    }

    private function recalculateQuote(int $taskId): void
    {
        $services = $this->serviceRepo->findAllByTaskId($taskId);

        $subtotal = array_reduce($services, fn($carry, $s) => $carry + $s->getTotalAmount(), 0.0);
        $tax = round($subtotal * 0.07, 2); // 7% tax
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
