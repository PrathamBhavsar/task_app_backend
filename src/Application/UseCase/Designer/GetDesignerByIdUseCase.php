<?php

namespace Application\UseCase\Designer;

use Domain\Repository\DesignerRepositoryInterface;
use Domain\Entity\Designer;

class GetDesignerByIdUseCase
{
    public function __construct(private DesignerRepositoryInterface $repo) {}

    public function execute(int $id): ?Designer
    {
        return $this->repo->findById($id);
    }
}
