<?php

namespace Interface\Controller;

use Application\UseCase\Designer\{
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

    public function index()
    {
        $designers = $this->getAll->execute();
        return JsonResponse::ok($designers);
    }

    public function show(int $id)
    {
        $designer = $this->getById->execute($id);
        return $designer
            ? JsonResponse::ok($designer)
            : JsonResponse::error("Designer not found", 404);
    }

    public function store(array $data)
    {
        $designer = $this->create->execute($data);
        return JsonResponse::ok($designer);
    }

    public function update(int $id, array $data)
    {
        $designer = $this->update->execute($id, $data);
        return $designer
            ? JsonResponse::ok($designer)
            : JsonResponse::error("Designer not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
