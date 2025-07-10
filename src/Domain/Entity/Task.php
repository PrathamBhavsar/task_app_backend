<?php

namespace Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "tasks")]
class Task implements \JsonSerializable

{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    private int $task_id;

    #[ORM\Column(type: "string")]
    private string $deal_no;

    #[ORM\Column(type: "string")]
    private string $name;

    #[ORM\Column(type: "datetime")]
    private \DateTime $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTime $start_date;

    #[ORM\Column(type: "datetime")]
    private \DateTime $due_date;

    #[ORM\Column(type: "string")]
    private string $priority;

    #[ORM\Column(type: "string")]
    private string $remarks;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "created_by", referencedColumnName: "user_id", nullable: false)]
    private User $created_by;

    #[ORM\ManyToOne(targetEntity: Client::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: "client_id", referencedColumnName: "client_id", nullable: false)]
    private Client $client;

    #[ORM\ManyToOne(targetEntity: Designer::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: "designer_id", referencedColumnName: "designer_id", nullable: false)]
    private Designer $designer;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "agency_id", referencedColumnName: "user_id", nullable: true)]
    private User $agency;

    public function __construct(
        string $dealNo,
        string $name,
        \DateTime $dueDate,
        string $priority,
        string $remarks,
        string $status,
        User $createdBy,
        Client $client,
        Designer $designer,
        User $agency
    ) {
        $this->deal_no = $dealNo;
        $this->name = $name;
        $this->created_at = new \DateTime();
        $this->start_date = new \DateTime();
        $this->due_date = $dueDate;
        $this->priority = $priority;
        $this->remarks = $remarks;
        $this->status = $status;
        $this->created_by = $createdBy;
        $this->client = $client;
        $this->designer = $designer;
        $this->agency = $agency;
    }

    public function jsonSerialize(): array
    {
        return [
            'task_id' => $this->getId(),
            'deal_no' => $this->getDealNo(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'start_date' => $this->getStartDate()->format('Y-m-d'),
            'due_date' => $this->getDueDate()->format('Y-m-d'),
            'priority' => $this->getPriority(),
            'remarks' => $this->getRemarks(),
            'status' => $this->getStatus(),
            'created_by' => $this->getCreatedBy(),
            'client' => $this->getClient(),
            'designer' => $this->getDesigner(),
            'agency' => $this->getAgency(),
        ];
    }

    // Getters
    public function getId(): int
    {
        return $this->task_id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDealNo(): string
    {
        return $this->deal_no;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->created_at;
    }

    public function getStartDate(): \DateTime
    {
        return $this->start_date;
    }

    public function getDueDate(): \DateTime
    {
        return $this->due_date;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getRemarks(): string
    {
        return $this->remarks;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedBy(): User
    {
        return $this->created_by;
    }

    public function getDesigner(): Designer
    {
        return $this->designer;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getAgency(): User
    {
        return $this->agency;
    }

    // Setters
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDealNo(string $dealNo): void
    {
        $this->deal_no = $dealNo;
    }

    public function setStartDate(\DateTime $startDate): void
    {
        $this->start_date = $startDate;
    }

    public function setDueDate(\DateTime $dueDate): void
    {
        $this->due_date = $dueDate;
    }

    public function setPriority(string $priority): void
    {
        $this->priority = $priority;
    }

    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setCreatedBy(User $createdBy): void
    {
        $this->created_by = $createdBy;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function setAgency(User $agency): void
    {
        $this->agency = $agency;
    }
}
