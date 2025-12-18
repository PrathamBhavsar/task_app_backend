<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Client\{
    CreateClientUseCase,
    GetAllClientsUseCase,
    GetClientByIdUseCase,
    UpdateClientUseCase,
    DeleteClientUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateClientRequest;
use Interface\Http\DTO\Request\UpdateClientRequest;
use Interface\Http\DTO\Response\ClientResponse;

class ClientController
{
    public function __construct(
        private GetAllClientsUseCase $getAll,
        private GetClientByIdUseCase $getById,
        private CreateClientUseCase $create,
        private UpdateClientUseCase $update,
        private DeleteClientUseCase $delete
    ) {}

    /**
     * Get all clients
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $clients = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $clientResponses = array_map(
            fn($client) => ClientResponse::fromEntity($client),
            $clients
        );
        
        return ApiResponse::collection($clientResponses, 'clients');
    }

    /**
     * Get a single client by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $client = $this->getById->execute($id);
        
        if (!$client) {
            return ApiResponse::notFound('Client not found');
        }
        
        $clientResponse = ClientResponse::fromEntity($client);
        return ApiResponse::success($clientResponse);
    }

    /**
     * Create a new client
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateClientRequest) {
            // Fallback: create DTO from request body
            $dto = CreateClientRequest::fromArray($request->body);
        }
        
        $client = $this->create->execute($dto->toArray());
        $clientResponse = ClientResponse::fromEntity($client);
        
        return ApiResponse::success($clientResponse, 201);
    }

    /**
     * Update an existing client
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateClientRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateClientRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $client = $this->update->execute($id, $data);
        
        if (!$client) {
            return ApiResponse::notFound('Client not found');
        }
        
        $clientResponse = ClientResponse::fromEntity($client);
        return ApiResponse::success($clientResponse);
    }

    /**
     * Delete a client
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if client exists before deleting
        $client = $this->getById->execute($id);
        
        if (!$client) {
            return ApiResponse::notFound('Client not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Client deleted successfully'
        ]);
    }
}
