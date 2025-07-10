<?php

namespace Application\UseCase\Quote;

use Domain\Repository\QuoteRepositoryInterface;

class DeleteQuoteUseCase
{
    public function __construct(private QuoteRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $quote = $this->repo->findById($id);
        if ($quote) {
            $this->repo->delete($quote);
        }
    }
}
