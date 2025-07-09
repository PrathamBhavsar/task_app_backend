<?php

namespace Application\UseCase\Timeline;

use Domain\Repository\TimelineRepositoryInterface;

class GetAllTimelinesUseCase
{
    public function __construct(private TimelineRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
