<?php

declare(strict_types=1);

namespace Interface\Controller;

use Application\UseCase\Timeline\{
    CreateTimelineUseCase,
    GetAllTimelinesUseCase,
    GetAllTimelinesByTaskIdUseCase,
    GetTimelineByIdUseCase,
    UpdateTimelineUseCase,
    DeleteTimelineUseCase
};
use Framework\Http\Request;
use Framework\Http\Response;
use Interface\Http\DTO\ApiResponse;
use Interface\Http\DTO\Request\CreateTimelineRequest;
use Interface\Http\DTO\Request\UpdateTimelineRequest;
use Interface\Http\DTO\Response\TimelineResponse;

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

    /**
     * Get all timelines
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $timelines = $this->getAll->execute();
        
        // Convert entities to response DTOs
        $timelineResponses = array_map(
            fn($timeline) => TimelineResponse::fromEntity($timeline),
            $timelines
        );
        
        return ApiResponse::collection($timelineResponses, 'timelines');
    }

    /**
     * Get timelines by task ID
     * 
     * @param Request $request
     * @return Response
     */
    public function getByTaskId(Request $request): Response
    {
        $taskId = (int) $request->getAttribute('task_id');
        $timelines = $this->getAllByTaskId->execute($taskId);
        
        // Convert entities to response DTOs
        $timelineResponses = array_map(
            fn($timeline) => TimelineResponse::fromEntity($timeline),
            $timelines
        );
        
        return ApiResponse::collection($timelineResponses, 'timelines');
    }

    /**
     * Get a single timeline by ID
     * 
     * @param Request $request
     * @return Response
     */
    public function show(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $timeline = $this->getById->execute($id);
        
        if (!$timeline) {
            return ApiResponse::notFound('Timeline not found');
        }
        
        $timelineResponse = TimelineResponse::fromEntity($timeline);
        return ApiResponse::success($timelineResponse);
    }

    /**
     * Create a new timeline
     * 
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof CreateTimelineRequest) {
            // Fallback: create DTO from request body
            $dto = CreateTimelineRequest::fromArray($request->body);
        }
        
        $timeline = $this->create->execute($dto->toArray());
        $timelineResponse = TimelineResponse::fromEntity($timeline);
        
        return ApiResponse::success($timelineResponse, 201);
    }

    /**
     * Update an existing timeline
     * 
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Get validated DTO from request attributes (set by ValidationMiddleware)
        $dto = $request->getAttribute('validated_dto');
        
        if (!$dto instanceof UpdateTimelineRequest) {
            // Fallback: create DTO from request body
            $dto = UpdateTimelineRequest::fromArray($request->body);
        }
        
        // Only pass provided fields to use case
        $data = $dto->getProvidedFields();
        
        if (empty($data)) {
            return ApiResponse::error('No fields provided for update', 400);
        }
        
        $timeline = $this->update->execute($id, $data);
        
        if (!$timeline) {
            return ApiResponse::notFound('Timeline not found');
        }
        
        $timelineResponse = TimelineResponse::fromEntity($timeline);
        return ApiResponse::success($timelineResponse);
    }

    /**
     * Delete a timeline
     * 
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->getAttribute('id');
        
        // Check if timeline exists before deleting
        $timeline = $this->getById->execute($id);
        
        if (!$timeline) {
            return ApiResponse::notFound('Timeline not found');
        }
        
        $this->delete->execute($id);
        
        return ApiResponse::success([
            'message' => 'Timeline deleted successfully'
        ]);
    }
}
