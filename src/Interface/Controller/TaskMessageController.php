<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\TaskMessage\{
    CreateTaskMessageUseCase,
    GetAllTaskMessagesByTaskIdUseCase,
    GetAllTaskMessagesUseCase,
    GetTaskMessageByIdUseCase,
    UpdateTaskMessageUseCase,
    DeleteTaskMessageUseCase
};
use Domain\Repository\UserRepositoryInterface;
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateTaskMessageRequest;
use Interface\Http\DTO\Request\UpdateTaskMessageRequest;
use Interface\Http\DTO\Response\TaskMessageResponse;

class TaskMessageController
{
    public function __construct(
        private GetAllTaskMessagesUseCase $getAll,
        private GetAllTaskMessagesByTaskIdUseCase $getAllByTaskId,
        private GetTaskMessageByIdUseCase $getById,
        private CreateTaskMessageUseCase $create,
        private UpdateTaskMessageUseCase $update,
        private DeleteTaskMessageUseCase $delete,
        private UserRepositoryInterface $userRepo
    ) {}

    /**
     * Get all task messages
     * Supports filtering by task_id via query parameter
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Check if filtering by task_id
        $taskId = $request->query['task_id'] ?? null;
        
        if ($taskId !== null) {
            $taskMessages = $this->getAllByTaskId->execute((int) $taskId);
        } else {
            $taskMessages = $this->getAll->execute();
        }
        
        // Convert entities to response DTOs
        $taskMessageResponses = array_map(
            fn($taskMessage) => TaskMessageResponse::fromEntity($taskMessage),
            $taskMessages
        );
        
        return ApiResponse::collection($taskMessageResponses, 'messages');
    }

    /**
     * Get task messages by task ID
     * 
     * @param Request $request
     * @return Response
     */
    public function getByTaskId(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $taskMessages = $this->getAllByTaskId->execute($taskId);
        
        // Convert entities to response DTOs
        $taskMessageResponses = array_map(
            fn($taskMessage) => TaskMessageResponse::fromEntity($taskMessage),
            $taskMessages
        );
        
        return ApiResponse::collection($taskMessageResponses, 'messages');
    }

    /**
     * Get a single task message by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $taskMessage = $this->getById->execute($id);
        
        if (!$taskMessage) {
            return ApiResponse::notFound('Task message not found');
        }
        
        $taskMessageResponse = TaskMessageResponse::fromEntity($taskMessage);
        return ApiResponse::success($taskMessageResponse);
    }

    /**
     * Create a new task message with task_id from URL
     * 
     * @param Request $request
     * @return Response
     */
    public function storeByTask(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $userId = $request->getAttribute('user_id');
        
        // Get message from request body
        $message = $request->body['message'] ?? '';
        
        if (empty($message)) {
            return ApiResponse::error('Message is required', 400);
        }
        
        // Get user from repository
        $user = $this->userRepo->findById($userId);
        
        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }
        
        // Create task message
        $taskMessage = $this->create->execute([
            'task_id' => $taskId,
            'message' => $message,
            'user' => $user
        ]);
        
        $taskMessageResponse = TaskMessageResponse::fromEntity($taskMessage);
        
        return ApiResponse::success($taskMessageResponse, 201);
    }

    /**
     * Create a new task message
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateTaskMessageRequest) {
            // Fallback: create DTO from request body
            $dto = CreateTaskMessageRequest::fromArray($request->body);
        }
        
        $taskMessage = $this->create->execute($dto->toArray());
        $taskMessageResponse = TaskMessageResponse::fromEntity($taskMessage);
        
        return ApiResponse::success($taskMessageResponse, 201);
    }

    /**
     * Update an existing task message
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateTaskMessageRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateTaskMessageRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $taskMessage = $this->update->execute($id, $data);
        
        if (!$taskMessage) {
            return ApiResponse::notFound('Task message not found');
        }
        
        $taskMessageResponse = TaskMessageResponse::fromEntity($taskMessage);
        return ApiResponse::success($taskMessageResponse);
    }

    /**
     * Delete a task message
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if task message exists before deleting
        $taskMessage = $this->getById->execute($id);
        
        if (!$taskMessage) {
            return ApiResponse::notFound('Task message not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Task message deleted successfully'
        ]);
    }
}
