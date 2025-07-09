<?php

namespace Application\UseCase\Timeline;

use Domain\Entity\Timeline;
use Domain\Repository\TimelineRepositoryInterface;
use Domain\Repository\UserRepositoryInterface;

class CreateTimelineUseCase
{
    public function __construct(
        private TimelineRepositoryInterface $timelineRepo,
        private UserRepositoryInterface $userRepo
    ) {}

    public function execute(array $data): Timeline
    {
        $user = $this->userRepo->findById($data['user_id']);
        if (!$user) {
            throw new \Exception("User not found");
        }

        $timeline = new Timeline(
            $data['task_id'],
            $data['status'],
            $user
        );

        return $this->timelineRepo->save($timeline);
    }
}
