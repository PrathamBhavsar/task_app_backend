<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Domain\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "task_timelines")]
class Timeline implements \JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $timeline_id;

    #[ORM\Column(type: "integer")]
    private int $task_id;

    #[ORM\Column(type: "string", length: 50)]
    private string $status;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "user_id", nullable: false)]
    private User $user;

    public function jsonSerialize(): array
    {
        return [
            'timeline_id' => $this->getId(),
            'task_id' => $this->getTaskId(),
            'status' => $this->getStatus(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'user' => $this->getUser()->jsonSerialize(),
        ];
    }

    public function __construct(int $taskId, string $status, User $user)
    {
        $this->task_id = $taskId;
        $this->status = $status;
        $this->user = $user;
        $this->created_at = new \DateTime();
    }

    // Getters
    public function getId(): int
    {
        return $this->timeline_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    // Setters
    public function setTaskId(int $task_id): void
    {
        $this->task_id = $task_id;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
