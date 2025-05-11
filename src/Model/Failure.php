<?php
namespace App\Model;

class Failure
{
    private array $original;
    public function __construct(array $data)
    {
        $this->original = $data;
    }

    public function toArray(): array
    {
        return $this->original;
    }
}
