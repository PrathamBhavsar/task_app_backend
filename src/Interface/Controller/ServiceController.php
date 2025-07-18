<?php

namespace Interface\Controller;

use Application\UseCase\Service\{
    CreateServiceUseCase,
    GetAllServicesUseCase,
    GetAllServicesByTaskIdUseCase,
    GetServiceByIdUseCase,
    UpdateServiceUseCase,
    DeleteServiceUseCase
};
use Interface\Http\JsonResponse;

class ServiceController
{
    public function __construct(
        private GetAllServicesUseCase $getAll,
        private GetAllServicesByTaskIdUseCase $getAllByTaskId,
        private GetServiceByIdUseCase $getById,
        private CreateServiceUseCase $create,
        private UpdateServiceUseCase $update,
        private DeleteServiceUseCase $delete
    ) {}

    public function index()
    {
        $services = $this->getAll->execute();
        return JsonResponse::list($services, 'services');
    }

    public function getByTaskId(int $taskId)
    {
        $services = $this->getAllByTaskId->execute($taskId);
        return JsonResponse::ok($services);
    }

    public function show(int $id)
    {
        $service = $this->getById->execute($id);
        return $service
            ? JsonResponse::ok($service)
            : JsonResponse::error("Service not found", 404);
    }

    public function store(array $data)
    {
        $service = $this->create->execute($data);
        return JsonResponse::ok($service);
    }

    public function update(int $id, array $data)
    {
        $service = $this->update->execute($id, $data);
        return $service
            ? JsonResponse::ok($service)
            : JsonResponse::error("Service not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
