<?php
namespace App\Model;

class Incident
{
    public string $description;
    public string $type = 'zgłoszenie awarii';
    public string $priority;
    public ?string $serviceDate;
    public string $status;
    public ?string $serviceNotes;
public ?string $phone;
    public string $createdAt;

    public function toArray(): array
    {
        return [
            'opis' => $this->description,
            'typ' => $this->type,
            'priorytet' => $this->priority,
            'termin wizyty serwisu' => $this->serviceDate,
            'status' => $this->status,
            'uwagi serwisu' => $this->serviceNotes,
            'numer telefonu osoby do kontaktu po stronie klienta' => $this->phone,
            'data utworzenia' => $this->createdAt,
        ];
    }
}