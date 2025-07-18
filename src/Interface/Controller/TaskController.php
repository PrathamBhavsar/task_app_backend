<?php

namespace Interface\Controller;

use Application\UseCase\Task\{
    CreateTaskUseCase,
    GetAllTasksUseCase,
    GetTaskByIdUseCase,
    UpdateTaskUseCase,
    UpdateTaskStatusUseCase,
    DeleteTaskUseCase
};
use Infrastructure\Auth\JwtService;
use Interface\Http\JsonResponse;

class TaskController
{
    public function __construct(
        private GetAllTasksUseCase $getAll,
        private GetTaskByIdUseCase $getById,
        private CreateTaskUseCase $create,
        private UpdateTaskUseCase $update,
        private UpdateTaskStatusUseCase $updateStatus,
        private DeleteTaskUseCase $delete,
        private JwtService $jwtService,
    ) {}

    private function getUserId(): ?int
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);
        return $this->jwtService->getUserIdFromToken($token);
    }

    public function index()
    {
        $userId = $this->getUserId();
        if (!$userId) return JsonResponse::unauthorized("Invalid or missing token");

        $tasks = $this->getAll->execute($userId);
        return JsonResponse::list($tasks, 'tasks');
    }

    public function show(int $id)
    {
        $userId = $this->getUserId();
        if (!$userId) return JsonResponse::unauthorized("Invalid or missing token");

        $task = $this->getById->execute($id, $userId);
        return $task
            ? JsonResponse::ok($task)
            : JsonResponse::error("Task not found", 404);
    }

    public function store(array $data)
    {
        $userId = $this->getUserId();
        if (!$userId) return JsonResponse::unauthorized("Invalid or missing token");

        $data['user_id'] = $userId;
        $task = $this->create->execute($data);
        return JsonResponse::ok($task);
    }

    public function update(int $id, array $data)
    {
        $userId = $this->getUserId();
        if (!$userId) return JsonResponse::unauthorized("Invalid or missing token");

        $task = $this->update->execute($id, $data, $userId);
        return $task
            ? JsonResponse::ok($task)
            : JsonResponse::error("Task not found", 404);
    }

    public function updateStatus(int $id, string $status)
    {
        $userId = $this->getUserId();
        if (!$userId) return JsonResponse::unauthorized("Invalid or missing token");

        $task = $this->updateStatus->execute($id, $status, $userId);
        return $task
            ? JsonResponse::ok($task)
            : JsonResponse::error("Task not found", 404);
    }

    public function delete(int $id)
    {
        $userId = $this->getUserId();
        if (!$userId) return JsonResponse::unauthorized("Invalid or missing token");

        $this->delete->execute($id, $userId);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
