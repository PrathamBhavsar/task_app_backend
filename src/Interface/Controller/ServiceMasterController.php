<?php

namespace Interface\Controller;

use Application\UseCase\ServiceMaster\{
    CreateServiceMasterUseCase,
    GetAllServiceMastersUseCase,
    GetServiceMasterByIdUseCase,
    UpdateServiceMasterUseCase,
    DeleteServiceMasterUseCase
};
use Interface\Http\JsonResponse;

class ServiceMasterController
{
    public function __construct(
        private GetAllServiceMastersUseCase $getAll,
        private GetServiceMasterByIdUseCase $getById,
        private CreateServiceMasterUseCase $create,
        private UpdateServiceMasterUseCase $update,
        private DeleteServiceMasterUseCase $delete
    ) {}

    public function index()
    {
        $serviceMasters = $this->getAll->execute();
        return JsonResponse::ok($serviceMasters);
    }

    public function show(int $id)
    {
        $serviceMaster = $this->getById->execute($id);
        return $serviceMaster
            ? JsonResponse::ok($serviceMaster)
            : JsonResponse::error("Service Master not found", 404);
    }

    public function store(array $data)
    {
        $serviceMaster = $this->create->execute($data);
        return JsonResponse::ok($serviceMaster);
    }

    public function update(int $id, array $data)
    {
        $serviceMaster = $this->update->execute($id, $data);
        return $serviceMaster
            ? JsonResponse::ok($serviceMaster)
            : JsonResponse::error("Service Master not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
