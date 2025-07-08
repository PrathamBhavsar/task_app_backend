<?php

namespace Application\UseCase;

use Domain\Repository\DesignerRepositoryInterface;

class DeleteDesignerUseCase
{
    public function __construct(private DesignerRepositoryInterface $repo) {}

    public function execute(int $id): void
    {
        $designer = $this->repo->findById($id);
        if ($designer) {
            $this->repo->delete($designer);
        }
    }
}
