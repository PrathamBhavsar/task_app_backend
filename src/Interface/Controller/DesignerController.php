<?php

namespace Interface\Controller;

use Application\UseCase\{
    CreateDesignerUseCase,
    GetAllDesignersUseCase,
    GetDesignerByIdUseCase,
    UpdateDesignerUseCase,
    DeleteDesignerUseCase
};
use Interface\Http\JsonResponse;

class DesignerController
{
    public function __construct(
        private GetAllDesignersUseCase $getAll,
        private GetDesignerByIdUseCase $getById,
        private CreateDesignerUseCase $create,
        private UpdateDesignerUseCase $update,
        private DeleteDesignerUseCase $delete
    ) {}

    private function serializeDesigner($designer): array
    {
        return [
            'designer_id' => $designer->getId(),
            'name' => $designer->getName(),
            'contact_no' => $designer->getContactNo(),
            'address' => $designer->getAddress(),
            'firm_name' => $designer->getFirmName(),
            'profile_bg_color' => $designer->getProfileBgColor(),
        ];
    }

    public function index()
    {
        $designers = $this->getAll->execute();
        $data = array_map([$this, 'serializeDesigner'], $designers);
        return JsonResponse::ok($data);
    }

    public function show(int $id)
    {
        $designer = $this->getById->execute($id);
        return $designer
            ? JsonResponse::ok($this->serializeDesigner($designer))
            : JsonResponse::error("Designer not found", 404);
    }

    public function store(array $data)
    {
        $designer = $this->create->execute($data);
        return JsonResponse::ok($this->serializeDesigner($designer));
    }

    public function update(int $id, array $data)
    {
        $designer = $this->update->execute($id, $data);
        return $designer
            ? JsonResponse::ok($this->serializeDesigner($designer))
            : JsonResponse::error("Designer not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
