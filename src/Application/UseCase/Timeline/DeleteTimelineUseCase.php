<?php

namespace Application\UseCase\Timeline;

use Domain\Repository\TimelineRepositoryInterface;

class DeleteTimelineUseCase
{
    public function __construct(private TimelineRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $timeline = $this->repo->findById($id);
        if ($timeline) {
            $this->repo->delete($timeline);
        }
    }
}
