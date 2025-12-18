<?php

declare(strict_types=1);

namespace Interface\Http\DTO\Response;

use Interface\Http\DTO\ResponseDTO;

/**
 * Response DTO for client data
 */
class ClientResponse extends ResponseDTO
{
    public function __construct(
        public readonly int $client_id,
        public readonly string $name,
        public readonly string $contact_no,
        public readonly string $address,
        public readonly string $email
    ) {}
    
    /**
     * Create a response DTO from a domain entity
     * 
     * @param object $client
     * @return static
     */
    public static function fromEntity(object $client): static
    {
        return new self(
            client_id: $client->getId(),
            name: $client->getName(),
            contact_no: $client->getContactNo(),
            address: $client->getAddress(),
            email: $client->getEmail()
        );
    }
}
