<?php
namespace Modules\Category\Repository;

use Modules\Category\Domain\Category;

interface CategoryRepositoryInterface
{
    /** @return Category[] */
    public function all(): array;

    public function find(int $id): ?Category;

    public function save(Category $category): Category;

    public function delete(int $id): bool;

    /**
     * Reorder categories. Accepts map of id => display_order.
     * @param array<int,int> $orders
     */
    public function reorder(array $orders): void;
}
