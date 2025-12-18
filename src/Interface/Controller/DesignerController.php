<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Designer\{
    CreateDesignerUseCase,
    GetAllDesignersUseCase,
    GetDesignerByIdUseCase,
    UpdateDesignerUseCase,
    DeleteDesignerUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateDesignerRequest;
use Interface\Http\DTO\Request\UpdateDesignerRequest;
use Interface\Http\DTO\Response\DesignerResponse;

class DesignerController
{
    public function __construct(
        private GetAllDesignersUseCase $getAll,
        private GetDesignerByIdUseCase $getById,
        private CreateDesignerUseCase $create,
        private UpdateDesignerUseCase $update,
        private DeleteDesignerUseCase $delete
    ) {}

    /**
     * Get all designers
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $designers = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $designerResponses = array_map(
            fn($designer) => DesignerResponse::fromEntity($designer),
            $designers
        );
        
        return ApiResponse::collection($designerResponses, 'designers');
    }

    /**
     * Get a single designer by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $designer = $this->getById->execute($id);
        
        if (!$designer) {
            return ApiResponse::notFound('Designer not found');
        }
        
        $designerResponse = DesignerResponse::fromEntity($designer);
        return ApiResponse::success($designerResponse);
    }

    /**
     * Create a new designer
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateDesignerRequest) {
            // Fallback: create DTO from request body
            $dto = CreateDesignerRequest::fromArray($request->body);
        }
        
        $designer = $this->create->execute($dto->toArray());
        $designerResponse = DesignerResponse::fromEntity($designer);
        
        return ApiResponse::success($designerResponse, 201);
    }

    /**
     * Update an existing designer
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateDesignerRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateDesignerRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $designer = $this->update->execute($id, $data);
        
        if (!$designer) {
            return ApiResponse::notFound('Designer not found');
        }
        
        $designerResponse = DesignerResponse::fromEntity($designer);
        return ApiResponse::success($designerResponse);
    }

    /**
     * Delete a designer
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if designer exists before deleting
        $designer = $this->getById->execute($id);
        
        if (!$designer) {
            return ApiResponse::notFound('Designer not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Designer deleted successfully'
        ]);
    }
}
