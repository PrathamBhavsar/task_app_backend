<?php

namespace Domain\Repository;

use Domain\Entity\Designer;

interface DesignerRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?Designer;
    public function save(Designer $designer): Designer;
    public function delete(Designer $designer): void;
}
