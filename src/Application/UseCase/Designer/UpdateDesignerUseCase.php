<?php

namespace Application\UseCase\Designer;

use Domain\Repository\DesignerRepositoryInterface;
use Domain\Entity\Designer;

class UpdateDesignerUseCase
{
    public function __construct(private DesignerRepositoryInterface $repo) {}

    public function execute(int $id, array $data): ?Designer
    {
        $designer = $this->repo->findById($id);
        if (!$designer) return null;

        $designer->setName($data['name']);
        $designer->setContactNo($data['contact_no']);
        $designer->setAddress($data['address']);
        $designer->setFirmName($data['firm_name']);
        $designer->setProfileBgColor($data['profile_bg_color']);

        return $this->repo->save($designer);
    }
}
