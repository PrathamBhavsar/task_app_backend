<?php

namespace Interface\Controller;

use Application\UseCase\Timeline\{
    CreateTimelineUseCase,
    GetAllTimelinesUseCase,
    GetAllTimelinesByTaskIdUseCase,
    GetTimelineByIdUseCase,
    UpdateTimelineUseCase,
    DeleteTimelineUseCase
};
use Interface\Http\JsonResponse;

class TimelineController
{
    public function __construct(
        private GetAllTimelinesUseCase $getAll,
        private GetAllTimelinesByTaskIdUseCase $getAllByTaskId,
        private GetTimelineByIdUseCase $getById,
        private CreateTimelineUseCase $create,
        private UpdateTimelineUseCase $update,
        private DeleteTimelineUseCase $delete
    ) {}

    public function index()
    {
        $timelines = $this->getAll->execute();
        return JsonResponse::list($timelines, 'timelines');
    }

    public function getByTaskId(int $taskId)
    {
        $timelines = $this->getAllByTaskId->execute($taskId);
        return JsonResponse::list($timelines, 'timelines');
    }


    public function show(int $id)
    {
        $timeline = $this->getById->execute($id);
        return $timeline
            ? JsonResponse::ok($timeline)
            : JsonResponse::error("Timeline not found", 404);
    }

    public function store(array $data)
    {
        $timeline = $this->create->execute($data);
        return JsonResponse::ok($timeline);
    }

    public function update(int $id, array $data)
    {
        $timeline = $this->update->execute($id, $data);
        return $timeline
            ? JsonResponse::ok($timeline)
            : JsonResponse::error("Timeline not found", 404);
    }

    public function delete(int $id)
    {
        $this->delete->execute($id);
        return JsonResponse::ok(['message' => 'Deleted successfully']);
    }
}
