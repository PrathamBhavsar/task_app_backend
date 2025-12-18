<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for quote data
 */
class QuoteResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $quote_id,
        public readonly int $task_id,
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $total,
        public readonly ?string $notes,
        public readonly string $created_at
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $quote
     * @return static
     */
    public static function fromEntity(object $quote): static
    {
        return new self(
            quote_id: $quote->getId(),
            task_id: $quote->getTaskId(),
            subtotal: $quote->getSubtotal(),
            tax: $quote->getTax(),
            total: $quote->getTotal(),
            notes: $quote->getNotes(),
            created_at: $quote->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
