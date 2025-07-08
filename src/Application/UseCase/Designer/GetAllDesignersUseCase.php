<?php

namespace Application\UseCase\Designer;

use Domain\Repository\DesignerRepositoryInterface;

class GetAllDesignersUseCase
{
    public function __construct(private DesignerRepositoryInterface $repo) {}

    public function execute(): array
    {
        return $this->repo->findAll();
    }
}
