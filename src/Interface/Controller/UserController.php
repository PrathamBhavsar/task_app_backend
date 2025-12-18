<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\User\{
    CreateUserUseCase,
    GetAllUsersUseCase,
    GetUserByIdUseCase,
    UpdateUserUseCase,
    DeleteUserUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateUserRequest;
use Interface\Http\DTO\Request\UpdateUserRequest;
use Interface\Http\DTO\Response\UserResponse;

class UserController
{
    public function __construct(
        private GetAllUsersUseCase $getAll,
        private GetUserByIdUseCase $getById,
        private CreateUserUseCase $create,
        private UpdateUserUseCase $update,
        private DeleteUserUseCase $delete
    ) {}

    /**
     * Get all users
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $users = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $userResponses = array_map(
            fn($user) => UserResponse::fromEntity($user),
            $users
        );
        
        return ApiResponse::collection($userResponses, 'users');
    }

    /**
     * Get a single user by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $user = $this->getById->execute($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found');
        }
        
        $userResponse = UserResponse::fromEntity($user);
        return ApiResponse::success($userResponse);
    }

    /**
     * Create a new user
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateUserRequest) {
            // Fallback: create DTO from request body
            $dto = CreateUserRequest::fromArray($request->body);
        }
        
        $user = $this->create->execute($dto->toArray());
        $userResponse = UserResponse::fromEntity($user);
        
        return ApiResponse::success($userResponse, 201);
    }

    /**
     * Update an existing user
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateUserRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateUserRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $user = $this->update->execute($id, $data);
        
        if (!$user) {
            return ApiResponse::notFound('User not found');
        }
        
        $userResponse = UserResponse::fromEntity($user);
        return ApiResponse::success($userResponse);
    }

    /**
     * Delete a user
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if user exists before deleting
        $user = $this->getById->execute($id);
        
        if (!$user) {
            return ApiResponse::notFound('User not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'User deleted successfully'
        ]);
    }
}
