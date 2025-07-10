<?php

namespace Application\UseCase\Quote;

use Domain\Repository\QuoteRepositoryInterface;

class GetAllQuotesUseCase
{
    public function __construct(private QuoteRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
