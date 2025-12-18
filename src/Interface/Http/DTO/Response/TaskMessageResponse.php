<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for task message data
 */
class TaskMessageResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $message_id,
        public readonly int $task_id,
        public readonly string $message,
        public readonly object $user,
        public readonly string $created_at
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $taskMessage
     * @return static
     */
    public static function fromEntity(object $taskMessage): static
    {
        return new self(
            message_id: $taskMessage->getId(),
            task_id: $taskMessage->getTaskId(),
            message: $taskMessage->getMessage(),
            user: $taskMessage->getUser(),
            created_at: $taskMessage->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
