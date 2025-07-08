<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $user_id;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "string")]
    private string $email;

    #[ORM\Column(type: "string")]
    private string $contact_no;

    #[ORM\Column(type: "datetime")]
    private \DateTime $created_at;

    #[ORM\Column(type: "string")]
    private string $user_type;

    #[ORM\Column(type: "string")]
    private string $address;

    #[ORM\Column(type: "string")]
    private string $profile_bg_color;

    public function __construct(
        string $name,
        string $contactNo,
        string $address,
        string $email,
        string $user_type,
        string $profile_bg_color
    ) {
        $this->name = $name;
        $this->contact_no = $contactNo;
        $this->address = $address;
        $this->email = $email;
        $this->user_type = $user_type;
        $this->created_at = new \DateTime();
        $this->profile_bg_color = $profile_bg_color;
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'contact_no' => $this->contact_no,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user_type' => $this->user_type,
            'address' => $this->address,
            'profile_bg_color' => $this->profile_bg_color,
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->user_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContactNo(): string
    {
        return $this->contact_no;
    }


    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getUserType(): string
    {
        return $this->user_type;
    }

    public function getProfileBgColor(): string
    {
        return $this->profile_bg_color;
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

    public function setUserType(string $user_type): void
    {
        $this->user_type = $user_type;
    }

    public function setProfileBgColor(string $profile_bg_color): void
    {
        $this->profile_bg_color = $profile_bg_color;
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
