<?php
namespace Modules\Category\Domain;

class Category
{
    public ?int $id;
    public string $name;
    public int $displayOrder;
    public ?string $createdAt;

    public function __construct(?int $id, string $name, int $displayOrder = 0, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->displayOrder = $displayOrder;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_order' => $this->displayOrder,
            'created_at' => $this->createdAt,
        ];
    }
}
