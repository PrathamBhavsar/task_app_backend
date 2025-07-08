<?php

namespace Application\UseCase;

use Domain\Entity\Designer;
use Domain\Repository\DesignerRepositoryInterface;

class CreateDesignerUseCase
{
    public function __construct(private DesignerRepositoryInterface $repo) {}

    public function execute(array $data): Designer
    {

        $designer = new Designer(
            name: $data['name'],
            contactNo: $data['contact_no'],
            address: $data['address'],
            firmName: $data['firm_name'],
            profileBgColor: $data['profile_bg_color']
        );

        return $this->repo->save($designer);
    }
}
