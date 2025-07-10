<?php

namespace Application\UseCase\Quote;

use Domain\Entity\Quote;
use Domain\Repository\QuoteRepositoryInterface;

class CreateQuoteUseCase
{
    public function __construct(private QuoteRepositoryInterface $repo) {}

    public function execute(array $data): Quote
    {

        $quote = new Quote(
            taskId: $data['task_id'],
            subtotal: $data['subtotal'],
            tax: $data['tax'],
            total: $data['total'],
            notes: $data['notes']
        );

        return $this->repo->save($quote);
    }
}
