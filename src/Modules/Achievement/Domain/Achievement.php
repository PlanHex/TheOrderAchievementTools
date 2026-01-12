<?php
namespace Modules\Achievement\Domain;

class Achievement
{
    public ?int $id;
    public int $categoryId;
    public string $title;
    public ?string $description;
    public int $points;
    public ?string $imageUrl;
    public int $displayOrder;
    public ?string $createdAt;

    public function __construct(?int $id, int $categoryId, string $title, ?string $description = null, int $points = 0, ?string $imageUrl = null, int $displayOrder = 0, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->description = $description;
        $this->points = $points;
        $this->imageUrl = $imageUrl;
        $this->displayOrder = $displayOrder;
        $this->createdAt = $createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'title' => $this->title,
            'description' => $this->description,
            'points' => $this->points,
            'image_url' => $this->imageUrl,
            'display_order' => $this->displayOrder,
            'created_at' => $this->createdAt,
        ];
    }
}
