<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Measurement\{
    CreateMeasurementUseCase,
    GetAllMeasurementsUseCase,
    GetAllMeasurementsByTaskIdUseCase,
    GetMeasurementByIdUseCase,
    UpdateMeasurementUseCase,
    DeleteMeasurementUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateMeasurementRequest;
use Interface\Http\DTO\Request\UpdateMeasurementRequest;
use Interface\Http\DTO\Response\MeasurementResponse;

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

    /**
     * Get all measurements
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $measurements = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $measurementResponses = array_map(
            fn($measurement) => MeasurementResponse::fromEntity($measurement),
            $measurements
        );
        
        return ApiResponse::collection($measurementResponses, 'measurements');
    }

    /**
     * Get measurements by task ID
     * 
     * @param Request $request
     * @return Response
     */
    public function getByTaskId(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $measurements = $this->getAllByTaskId->execute($taskId);
        
        // Convert entities to response DTOs
        $measurementResponses = array_map(
            fn($measurement) => MeasurementResponse::fromEntity($measurement),
            $measurements
        );
        
        return ApiResponse::collection($measurementResponses, 'measurements');
    }

    /**
     * Get a single measurement by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $measurement = $this->getById->execute($id);
        
        if (!$measurement) {
            return ApiResponse::notFound('Measurement not found');
        }
        
        $measurementResponse = MeasurementResponse::fromEntity($measurement);
        return ApiResponse::success($measurementResponse);
    }

    /**
     * Create a new measurement
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateMeasurementRequest) {
            // Fallback: create DTO from request body
            $dto = CreateMeasurementRequest::fromArray($request->body);
        }
        
        $measurement = $this->create->execute($dto->toArray());
        $measurementResponse = MeasurementResponse::fromEntity($measurement);
        
        return ApiResponse::success($measurementResponse, 201);
    }

    /**
     * Update an existing measurement
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateMeasurementRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateMeasurementRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $measurement = $this->update->execute($id, $data);
        
        if (!$measurement) {
            return ApiResponse::notFound('Measurement not found');
        }
        
        $measurementResponse = MeasurementResponse::fromEntity($measurement);
        return ApiResponse::success($measurementResponse);
    }

    /**
     * Delete a measurement
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if measurement exists before deleting
        $measurement = $this->getById->execute($id);
        
        if (!$measurement) {
            return ApiResponse::notFound('Measurement not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Measurement deleted successfully'
        ]);
    }
}
