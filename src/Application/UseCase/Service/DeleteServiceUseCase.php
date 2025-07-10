<?php

namespace Application\UseCase\Service;

use Domain\Repository\ServiceRepositoryInterface;

class DeleteServiceUseCase
{
    public function __construct(private ServiceRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $service = $this->repo->findById($id);
        if ($service) {
            $this->repo->delete($service);
        }
    }
}
