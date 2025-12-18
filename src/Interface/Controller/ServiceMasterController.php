<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\ServiceMaster\{
    CreateServiceMasterUseCase,
    GetAllServiceMastersUseCase,
    GetServiceMasterByIdUseCase,
    UpdateServiceMasterUseCase,
    DeleteServiceMasterUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateServiceMasterRequest;
use Interface\Http\DTO\Request\UpdateServiceMasterRequest;
use Interface\Http\DTO\Response\ServiceMasterResponse;

class ServiceMasterController
{
    public function __construct(
        private GetAllServiceMastersUseCase $getAll,
        private GetServiceMasterByIdUseCase $getById,
        private CreateServiceMasterUseCase $create,
        private UpdateServiceMasterUseCase $update,
        private DeleteServiceMasterUseCase $delete
    ) {}

    /**
     * Get all service masters
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $serviceMasters = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $serviceMasterResponses = array_map(
            fn($serviceMaster) => ServiceMasterResponse::fromEntity($serviceMaster),
            $serviceMasters
        );
        
        return ApiResponse::collection($serviceMasterResponses, 'service_masters');
    }

    /**
     * Get a single service master by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $serviceMaster = $this->getById->execute($id);
        
        if (!$serviceMaster) {
            return ApiResponse::notFound('Service Master not found');
        }
        
        $serviceMasterResponse = ServiceMasterResponse::fromEntity($serviceMaster);
        return ApiResponse::success($serviceMasterResponse);
    }

    /**
     * Create a new service master
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateServiceMasterRequest) {
            // Fallback: create DTO from request body
            $dto = CreateServiceMasterRequest::fromArray($request->body);
        }
        
        $serviceMaster = $this->create->execute($dto->toArray());
        $serviceMasterResponse = ServiceMasterResponse::fromEntity($serviceMaster);
        
        return ApiResponse::success($serviceMasterResponse, 201);
    }

    /**
     * Update an existing service master
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateServiceMasterRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateServiceMasterRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $serviceMaster = $this->update->execute($id, $data);
        
        if (!$serviceMaster) {
            return ApiResponse::notFound('Service Master not found');
        }
        
        $serviceMasterResponse = ServiceMasterResponse::fromEntity($serviceMaster);
        return ApiResponse::success($serviceMasterResponse);
    }

    /**
     * Delete a service master
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if service master exists before deleting
        $serviceMaster = $this->getById->execute($id);
        
        if (!$serviceMaster) {
            return ApiResponse::notFound('Service Master not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Service Master deleted successfully'
        ]);
    }
}
