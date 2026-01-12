<?php
namespace Modules\User\Domain;

class User
{
    public ?int $id;
    public string $name;
    public ?string $createdAt;

    public function __construct(?int $id, string $name, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->createdAt,
        ];
    }
}
