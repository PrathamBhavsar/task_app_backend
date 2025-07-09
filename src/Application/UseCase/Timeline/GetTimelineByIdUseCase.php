<?php

namespace Application\UseCase\Timeline;

use Domain\Repository\TimelineRepositoryInterface;
use Domain\Entity\Timeline;

class GetTimelineByIdUseCase
{
    public function __construct(private TimelineRepositoryInterface $repo) {}

    public function execute(int $id): ?Timeline
    {
        return $this->repo->findById($id);
    }
}
