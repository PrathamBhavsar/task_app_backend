<?php

namespace Interface\Controller;

use Application\UseCase\Measurement\{
    CreateMeasurementUseCase,
    GetAllMeasurementsUseCase,
    GetAllMeasurementsByTaskIdUseCase,
    GetMeasurementByIdUseCase,
    UpdateMeasurementUseCase,
    DeleteMeasurementUseCase
};
use Interface\Http\JsonResponse;

class MeasurementController
{
    public function __construct(
        private GetAllMeasurementsUseCase $getAll,
        private GetAllMeasurementsByTaskIdUseCase $getAllByTaskId,
        private GetMeasurementByIdUseCase $getById,
        private CreateMeasurementUseCase $create,
        private UpdateMeasurementUseCase $update,
        private DeleteMeasurementUseCase $delete
    ) {}

    public function index()
    {
        $measurements = $this->getAll->execute();
        return JsonResponse::ok($measurements);
    }

    public function getByTaskId(int $taskId)
    {
        $measurements = $this->getAllByTaskId->execute($taskId);
        return JsonResponse::ok($measurements);
    }

    public function show(int $id)
    {
        $measurement = $this->getById->execute($id);
        return $measurement
            ? JsonResponse::ok($measurement)
            : JsonResponse::error("Measurement not found", 404);
    }

    public function store(array $data)
    {
        $measurement = $this->create->execute($data);
        return JsonResponse::ok($measurement);
    }

    public function update(int $id, array $data)
    {
        $measurement = $this->update->execute($id, $data);
        return $measurement
            ? JsonResponse::ok($measurement)
            : JsonResponse::error("Measurement not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
