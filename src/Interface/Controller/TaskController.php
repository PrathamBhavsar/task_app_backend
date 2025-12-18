<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Task\{
    CreateTaskUseCase,
    GetAllTasksUseCase,
    GetTaskByIdUseCase,
    UpdateTaskUseCase,
    UpdateTaskStatusUseCase,
    DeleteTaskUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateTaskRequest;
use Interface\Http\DTO\Request\UpdateTaskRequest;
use Interface\Http\DTO\Response\TaskResponse;

class TaskController
{
    public function __construct(
        private GetAllTasksUseCase $getAll,
        private GetTaskByIdUseCase $getById,
        private CreateTaskUseCase $create,
        private UpdateTaskUseCase $update,
        private UpdateTaskStatusUseCase $updateStatus,
        private DeleteTaskUseCase $delete
    ) {}

    /**
     * Get all tasks
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }

        $tasks = $this->getAll->execute($userId);
        
        // Convert entities to response DTOs
        $taskResponses = array_map(
            fn($task) => TaskResponse::fromEntity($task),
            $tasks
        );
        
        return ApiResponse::collection($taskResponses, 'tasks');
    }

    /**
     * Get a single task by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }

        $task = $this->getById->execute($id, $userId);
        
        if (!$task) {
            return ApiResponse::notFound('Task not found');
        }
        
        $taskResponse = TaskResponse::fromEntity($task);
        return ApiResponse::success($taskResponse);
    }

    /**
     * Create a new task
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }

        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateTaskRequest) {
            // Fallback: create DTO from request body
            $dto = CreateTaskRequest::fromArray($request->body);
        }
        
        $data = $dto->toArray();
        $data['user_id'] = $userId;
        
        $task = $this->create->execute($data);
        $taskResponse = TaskResponse::fromEntity($task);
        
        return ApiResponse::success($taskResponse, 201);
    }

    /**
     * Update an existing task
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }

        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateTaskRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateTaskRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $task = $this->update->execute($id, $data, $userId);
        
        if (!$task) {
            return ApiResponse::notFound('Task not found');
        }
        
        $taskResponse = TaskResponse::fromEntity($task);
        return ApiResponse::success($taskResponse);
    }

    /**
     * Update task status
     * 
     * @param Request $request
     * @return Response
     */
    public function updateStatus(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $userId = $request->getAttribute('user_id');
        $status = $request->body['status'] ?? $request->query['status'] ?? null;
        
        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }
        
        if (!$status) {
            return ApiResponse::error('Status is required', 400);
        }
        
        $task = $this->updateStatus->execute($id, $status, $userId);
        
        if (!$task) {
            return ApiResponse::notFound('Task not found');
        }
        
        $taskResponse = TaskResponse::fromEntity($task);
        return ApiResponse::success($taskResponse);
    }

    /**
     * Delete a task
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $userId = $request->getAttribute('user_id');
        
        if (!$userId) {
            return ApiResponse::error('Unauthorized', 401);
        }

        // Check if task exists before deleting
        $task = $this->getById->execute($id, $userId);
        
        if (!$task) {
            return ApiResponse::notFound('Task not found');
        }
        
        $this->delete->execute($id, $userId);
        
        return ApiResponse::success([
            'message' => 'Task deleted successfully'
        ]);
    }
}
