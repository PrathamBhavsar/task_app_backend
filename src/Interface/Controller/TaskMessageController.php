<?php

namespace Interface\Controller;

use Application\UseCase\TaskMessage\{
    CreateTaskMessageUseCase,
    GetAllTaskMessagesByTaskIdUseCase,
    GetAllTaskMessagesUseCase,
    GetTaskMessageByIdUseCase,
    UpdateTaskMessageUseCase,
    DeleteTaskMessageUseCase
};
use Interface\Http\JsonResponse;

class TaskMessageController
{
    public function __construct(
        private GetAllTaskMessagesUseCase $getAll,
        private GetAllTaskMessagesByTaskIdUseCase $getAllByTaskId,
        private GetTaskMessageByIdUseCase $getById,
        private CreateTaskMessageUseCase $create,
        private UpdateTaskMessageUseCase $update,
        private DeleteTaskMessageUseCase $delete
    ) {}

    public function index()
    {
        $taskMessages = $this->getAll->execute();
        return JsonResponse::ok($taskMessages);
    }

    public function getByTaskId(int $taskId)
    {
        $timelines = $this->getAllByTaskId->execute($taskId);
        return JsonResponse::ok($timelines);
    }

    public function show(int $id)
    {
        $taskMessage = $this->getById->execute($id);
        return $taskMessage
            ? JsonResponse::ok($taskMessage)
            : JsonResponse::error("TaskMessage not found", 404);
    }

    public function store(array $data)
    {
        $taskMessage = $this->create->execute($data);
        return JsonResponse::ok($taskMessage);
    }

    public function update(int $id, array $data)
    {
        $taskMessage = $this->update->execute($id, $data);
        return $taskMessage
            ? JsonResponse::ok($taskMessage)
            : JsonResponse::error("TaskMessage not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
