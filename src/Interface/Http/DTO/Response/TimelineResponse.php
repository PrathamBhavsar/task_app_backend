<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for timeline data
 */
class TimelineResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $timeline_id,
        public readonly int $task_id,
        public readonly string $status,
        public readonly string $created_at,
        public readonly object $user
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $timeline
     * @return static
     */
    public static function fromEntity(object $timeline): static
    {
        return new self(
            timeline_id: $timeline->getId(),
            task_id: $timeline->getTaskId(),
            status: $timeline->getStatus(),
            created_at: $timeline->getCreatedAt()->format('Y-m-d H:i:s'),
            user: $timeline->getUser()
        );
    }
}
