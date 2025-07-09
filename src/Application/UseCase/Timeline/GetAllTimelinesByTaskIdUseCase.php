<?php

namespace Application\UseCase\Timeline;

use Domain\Repository\TimelineRepositoryInterface;

class GetAllTimelinesByTaskIdUseCase
{
    public function __construct(private TimelineRepositoryInterface $repo) {}

    public function execute(int $taskId): array
    {
        return $this->repo->findAllByTaskId($taskId);
    }
}
