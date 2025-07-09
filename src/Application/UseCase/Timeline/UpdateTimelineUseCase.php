<?php

namespace Application\UseCase\Timeline;

use Domain\Repository\TimelineRepositoryInterface;
use Domain\Entity\Timeline;
use Domain\Repository\UserRepositoryInterface;

class UpdateTimelineUseCase
{
    public function __construct(
        private TimelineRepositoryInterface $repo,
        private UserRepositoryInterface $userRepo
    ) {}

    public function execute(int $id, array $data): ?Timeline
    {
        $timeline = $this->repo->findById($id);
        if (!$timeline) return null;

        $user = $this->userRepo->findById($data['user_id']);
        if (!$user) return null;

        $timeline->setTaskId($data['task_id']);
        $timeline->setStatus($data['status']);
        $timeline->setUser($user);

        return $this->repo->save($timeline);
    }
}
