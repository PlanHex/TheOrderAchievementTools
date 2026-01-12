<?php
namespace Modules\Achievement\Repository;

use Modules\Achievement\Domain\Achievement;

interface AchievementRepositoryInterface
{
    /** @return Achievement[] */
    public function all(?int $categoryId = null): array;

    public function find(int $id): ?Achievement;

    public function save(Achievement $achievement): Achievement;

    public function delete(int $id): bool;

    /**
     * Reorder achievements within a category. Accepts map of id => display_order.
     * @param array<int,int> $orders
     */
    public function reorder(array $orders): void;
}
