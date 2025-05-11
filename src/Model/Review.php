<?php
namespace App\Model;

class Review
{
    public string $description;
    public string $type = 'przegląd';
    public string $reviewDate;
    public int $week;
    public string $status;
    public ?string $recommendations;
    public ?string $phone;
    public string $createdAt;

    public function toArray(): array
    {
        return [
            'opis' => $this->description,
            'typ' => $this->type,
            'data przeglądu' => $this->reviewDate,
            'tydzień w roku daty przeglądu' => $this->week,
            'status' => $this->status,
            'zalecenia dalszej obsługi po przeglądzie' => $this->recommendations,
            'numer telefonu osoby do kontaktu po stronie klienta' => $this->phone,
            'data utworzenia' => $this->createdAt,
        ];
    }
}
