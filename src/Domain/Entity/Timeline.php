<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Domain\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "task_timelines")]
class Timeline
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
}
