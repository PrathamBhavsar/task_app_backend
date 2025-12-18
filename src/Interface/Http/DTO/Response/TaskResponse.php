<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for task data
 */
class TaskResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $task_id,
        public readonly string $deal_no,
        public readonly string $name,
        public readonly string $created_at,
        public readonly string $start_date,
        public readonly string $due_date,
        public readonly string $priority,
        public readonly string $remarks,
        public readonly string $status,
        public readonly object $created_by,
        public readonly object $client,
        public readonly object $designer,
        public readonly ?object $agency
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $task
     * @return static
     */
    public static function fromEntity(object $task): static
    {
        return new self(
            task_id: $task->getId(),
            deal_no: $task->getDealNo(),
            name: $task->getName(),
            created_at: $task->getCreatedAt()->format('Y-m-d H:i:s'),
            start_date: $task->getStartDate()->format('Y-m-d'),
            due_date: $task->getDueDate()->format('Y-m-d'),
            priority: $task->getPriority(),
            remarks: $task->getRemarks(),
            status: $task->getStatus(),
            created_by: $task->getCreatedBy(),
            client: $task->getClient(),
            designer: $task->getDesigner(),
            agency: $task->getAgency()
        );
    }
}
