<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "task_messages")]
class TaskMessage implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $message_id;

    #[ORM\Column(type: "integer")]
    private int $task_id;

    #[ORM\Column(type: "string")]
    private string $message;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: false)]
    private User $user;

    #[ORM\Column(type: "datetime")]
    private \DateTime $created_at;

    public function __construct(
        string $taskId,
        string $message,
        User $user
    ) {
        $this->task_id = $taskId;
        $this->message = $message;
        $this->user = $user;
        $this->created_at = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'message_id' => $this->getId(),
            'message' => $this->getMessage(),
            'user' => $this->getUser()->jsonSerialize(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->message_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }

    // Setters
    public function setTaskId(int $task_id): void
    {
        $this->task_id = $task_id;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
