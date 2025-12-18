<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Example;

use Interface\Http\DTO\ResponseDTO;

/**
 * Example Response DTO for client data
 * 
 * This demonstrates the usage pattern for Response DTOs with automatic JSON serialization.
 */
class ClientResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $contact_no,
        public readonly string $address,
        public readonly string $email,
        public readonly string $created_at,
        public readonly ?string $updated_at = null
    ) {}
    
    /**
     * Create a response DTO from a domain entity or array
     * 
     * @param object|array $client
     * @return static
     */
    public static function fromEntity(object|array $client): static
    {
        if (is_array($client)) {
            return new self(
                id: $client['id'],
                name: $client['name'],
                contact_no: $client['contact_no'],
                address: $client['address'],
                email: $client['email'],
                created_at: $client['created_at'],
                updated_at: $client['updated_at'] ?? null
            );
        }
        
        return new self(
            id: $client->getId(),
            name: $client->getName(),
            contact_no: $client->getContactNo(),
            address: $client->getAddress(),
            email: $client->getEmail(),
            created_at: $client->getCreatedAt()->format('Y-m-d H:i:s'),
            updated_at: $client->getUpdatedAt()?->format('Y-m-d H:i:s')
        );
    }
}
