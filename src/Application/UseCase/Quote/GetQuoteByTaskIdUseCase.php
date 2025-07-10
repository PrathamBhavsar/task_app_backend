<?php

namespace Application\UseCase\Quote;

use Domain\Repository\QuoteRepositoryInterface;
use Domain\Entity\Quote;

class GetQuoteByTaskIdUseCase
{
    public function __construct(private QuoteRepositoryInterface $repo) {}

    public function execute(int $taskId): Quote
    {
        return $this->repo->findByTaskId($taskId);
    }
}
