<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "clients")]
class Client
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $client_id;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "string")]
    private string $contact_no;

    #[ORM\Column(type: "string")]
    private string $address;

    #[ORM\Column(type: "string")]
    private string $email;

    public function __construct(
        string $name,
        string $contactNo,
        string $address,
        string $email
    ) {
        $this->name = $name;
        $this->contact_no = $contactNo;
        $this->address = $address;
        $this->email = $email;
    }

    // Getters
    public function getId(): int
    {
        return $this->client_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContactNo(): string
    {
        return $this->contact_no;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    // Setters
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setContactNo(string $contactNo): void
    {
        $this->contact_no = $contactNo;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
