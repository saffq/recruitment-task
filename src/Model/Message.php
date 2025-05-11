<?php
namespace App\Model;

class Message
{
    private string $description;
    private ?string $dueDate;
    private ?string $phone;

    public function __construct(array $data)
    {
        $this->description = $data['description'] ?? '';
        $this->dueDate = $data['dueDate'] ?? null;
        $this->phone = $data['phone'] ?? null;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate ? new \DateTimeImmutable($this->dueDate) : null;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}