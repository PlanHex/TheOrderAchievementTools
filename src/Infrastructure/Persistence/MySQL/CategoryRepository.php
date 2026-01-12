<?php
namespace Infrastructure\Persistence\MySQL;

use Core\Database;
use Modules\Category\Domain\Category;
use Modules\Category\Repository\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return Category[] */
    public function all(): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM categories ORDER BY display_order ASC, id ASC');
        $out = [];
        foreach ($rows as $r) {
            $out[] = new Category((int)$r['id'], $r['name'], (int)$r['display_order'], $r['created_at'] ?? null);
        }
        return $out;
    }

    public function find(int $id): ?Category
    {
        $r = $this->db->fetch('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
        if (!$r) return null;
        return new Category((int)$r['id'], $r['name'], (int)$r['display_order'], $r['created_at'] ?? null);
    }

    public function save(Category $category): Category
    {
        if ($category->id === null) {
            $this->db->execute('INSERT INTO categories (name, display_order) VALUES (:name, :display_order)', ['name' => $category->name, 'display_order' => $category->displayOrder]);
            $category->id = (int)$this->db->pdo()->lastInsertId();
            return $category;
        }

        $this->db->execute('UPDATE categories SET name = :name, display_order = :display_order WHERE id = :id', ['name' => $category->name, 'display_order' => $category->displayOrder, 'id' => $category->id]);
        return $category;
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db->execute('DELETE FROM categories WHERE id = :id', ['id' => $id]);
    }

    public function reorder(array $orders): void
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('UPDATE categories SET display_order = :display WHERE id = :id');
        foreach ($orders as $id => $display) {
            $stmt->execute(['display' => (int)$display, 'id' => (int)$id]);
        }
    }
}
