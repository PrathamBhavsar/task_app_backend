<?php

namespace Application\UseCase\Quote;

use Domain\Repository\QuoteRepositoryInterface;
use Domain\Entity\Quote;

class UpdateQuoteUseCase
{
    public function __construct(private QuoteRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Quote
    {
        $quote = $this->repo->findById($id);
        if (!$quote) return null;

        $quote->setTaskId($data['task_id']);
        $quote->setSubtotal($data['subtotal']);
        $quote->setTax($data['tax']);
        $quote->setTotal($data['total']);
        $quote->setNotes($data['notes']);

        return $this->repo->save($quote);
    }
}
