<?php

namespace Application\UseCase\Quote;

use Domain\Entity\Quote;
use Domain\Repository\QuoteRepositoryInterface;
use Domain\Repository\ServiceRepositoryInterface;
use Domain\Repository\MeasurementRepositoryInterface;

class CreateQuoteUseCase
{
    public function __construct(
        private QuoteRepositoryInterface $quoteRepo,
        private ServiceRepositoryInterface $serviceRepo,
        private MeasurementRepositoryInterface $measurementRepo
    ) {}

    public function execute(array $data): Quote
    {
        $taskId = $data['task_id'];

        $services = $this->serviceRepo->findAllByTaskId($taskId);
        $measurements = $this->measurementRepo->findAllByTaskId($taskId);

        $serviceTotal = array_reduce($services, fn($carry, $s) => $carry + $s->getTotalAmount(), 0.0);
        $measurementTotal = array_reduce($measurements, fn($carry, $m) => $carry + $m->getTotalPrice(), 0.0);

        $subtotal = $serviceTotal + $measurementTotal;
        $tax = round($subtotal * 0.07, 2);
        $total = $subtotal + $tax;

        $quote = new Quote(
            taskId: $taskId,
            subtotal: $subtotal,
            tax: $tax,
            total: $total,
            notes: $data['notes'] ?? null
        );

        return $this->quoteRepo->save($quote);
    }
}
