<?php

namespace Interface\Controller;

use Application\UseCase\Task\{
    CreateTaskUseCase,
    GetAllTasksUseCase,
    GetTaskByIdUseCase,
    UpdateTaskUseCase,
    DeleteTaskUseCase
};
use Interface\Http\JsonResponse;

class TaskController
{
    public function __construct(
        private GetAllTasksUseCase $getAll,
        private GetTaskByIdUseCase $getById,
        private CreateTaskUseCase $create,
        private UpdateTaskUseCase $update,
        private DeleteTaskUseCase $delete
    ) {}

    public function index()
    {
        $tasks = $this->getAll->execute();
        return JsonResponse::ok($tasks);
    }

    public function show(int $id)
    {
        $task = $this->getById->execute($id);
        return $task
            ? JsonResponse::ok($task)
            : JsonResponse::error("Task not found", 404);
    }

    public function store(array $data)
    {
        $task = $this->create->execute($data);
        return JsonResponse::ok($task);
    }

    public function update(int $id, array $data)
    {
        $task = $this->update->execute($id, $data);
        return $task
            ? JsonResponse::ok($task)
            : JsonResponse::error("Task not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
