<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Service\{
    CreateServiceUseCase,
    GetAllServicesUseCase,
    GetAllServicesByTaskIdUseCase,
    GetServiceByIdUseCase,
    UpdateServiceUseCase,
    DeleteServiceUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateServiceRequest;
use Interface\Http\DTO\Request\UpdateServiceRequest;
use Interface\Http\DTO\Response\ServiceResponse;

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

    /**
     * Get all services
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $services = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $serviceResponses = array_map(
            fn($service) => ServiceResponse::fromEntity($service),
            $services
        );
        
        return ApiResponse::collection($serviceResponses, 'services');
    }

    /**
     * Get services by task ID
     * 
     * @param Request $request
     * @return Response
     */
    public function getByTaskId(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $services = $this->getAllByTaskId->execute($taskId);
        
        // Convert entities to response DTOs
        $serviceResponses = array_map(
            fn($service) => ServiceResponse::fromEntity($service),
            $services
        );
        
        return ApiResponse::collection($serviceResponses, 'services');
    }

    /**
     * Get a single service by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $service = $this->getById->execute($id);
        
        if (!$service) {
            return ApiResponse::notFound('Service not found');
        }
        
        $serviceResponse = ServiceResponse::fromEntity($service);
        return ApiResponse::success($serviceResponse);
    }

    /**
     * Create a new service
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateServiceRequest) {
            // Fallback: create DTO from request body
            $dto = CreateServiceRequest::fromArray($request->body);
        }
        
        $service = $this->create->execute($dto->toArray());
        $serviceResponse = ServiceResponse::fromEntity($service);
        
        return ApiResponse::success($serviceResponse, 201);
    }

    /**
     * Update an existing service
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateServiceRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateServiceRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $service = $this->update->execute($id, $data);
        
        if (!$service) {
            return ApiResponse::notFound('Service not found');
        }
        
        $serviceResponse = ServiceResponse::fromEntity($service);
        return ApiResponse::success($serviceResponse);
    }

    /**
     * Delete a service
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if service exists before deleting
        $service = $this->getById->execute($id);
        
        if (!$service) {
            return ApiResponse::notFound('Service not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Service deleted successfully'
        ]);
    }
}
