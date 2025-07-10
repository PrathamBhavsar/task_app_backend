<?php

namespace Application\UseCase\Quote;

use Domain\Repository\QuoteRepositoryInterface;
use Domain\Entity\Quote;

class GetQuoteByIdUseCase
{
    public function __construct(private QuoteRepositoryInterface $repo) {}

    public function execute(int $id): ?Quote
    {
        return $this->repo->findById($id);
    }
}
