<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for bill data
 */
class BillResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $bill_id,
        public readonly int $task_id,
        public readonly string $created_at,
        public readonly string $due_date,
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $total,
        public readonly string $status,
        public readonly ?string $additional_notes
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $bill
     * @return static
     */
    public static function fromEntity(object $bill): static
    {
        return new self(
            bill_id: $bill->getId(),
            task_id: $bill->getTaskId(),
            created_at: $bill->getCreatedAt()->format('Y-m-d H:i:s'),
            due_date: $bill->getDueDate()->format('Y-m-d H:i:s'),
            subtotal: $bill->getSubtotal(),
            tax: $bill->getTax(),
            total: $bill->getTotal(),
            status: $bill->getStatus(),
            additional_notes: $bill->getNotes()
        );
    }
}
