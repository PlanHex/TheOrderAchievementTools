<?php
namespace Infrastructure\Persistence\InMemory;

use Modules\Category\Domain\Category;
use Modules\Category\Repository\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    private const SESSION_KEY = 'inmemory_categories';
    private CsvLoader $loader;
    private string $dataDir;

    public function __construct(string $dataDir)
    {
        $this->dataDir = rtrim($dataDir, "\\/");
        $this->loader = new CsvLoader($this->dataDir);
        if (!isset($_SESSION[self::SESSION_KEY])) {
            $this->seed();
        }
    }

    private function seed(): void
    {
        $rows = $this->loader->load('categories.csv');
        $out = [];
        foreach ($rows as $r) {
            $id = isset($r['id']) ? (int)$r['id'] : null;
            $out[$id ?? uniqid('c', true)] = [
                'id' => $id,
                'name' => $r['name'] ?? '',
                'display_order' => isset($r['display_order']) ? (int)$r['display_order'] : 0,
                'created_at' => $r['created_at'] ?? null,
            ];
        }

        $_SESSION[self::SESSION_KEY] = $out;
    }

    /** @return Category[] */
    public function all(): array
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        usort($rows, function ($a, $b) {
            return ($a['display_order'] <=> $b['display_order']) ?: ($a['id'] <=> $b['id']);
        });

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Category($r['id'], $r['name'], (int)$r['display_order'], $r['created_at'] ?? null);
        }

        return $out;
    }

    public function find(int $id): ?Category
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        foreach ($rows as $r) {
            if ((int)$r['id'] === $id) {
                return new Category($r['id'], $r['name'], (int)$r['display_order'], $r['created_at'] ?? null);
            }
        }
        return null;
    }

    public function save(Category $category): Category
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        if ($category->id === null) {
            $max = 0;
            foreach ($rows as $r) {
                $max = max($max, (int)$r['id']);
            }
            $category->id = $max + 1;
        }

        $_SESSION[self::SESSION_KEY][$category->id] = [
            'id' => $category->id,
            'name' => $category->name,
            'display_order' => $category->displayOrder,
            'created_at' => $category->createdAt,
        ];

        return $category;
    }

    public function delete(int $id): bool
    {
        if (isset($_SESSION[self::SESSION_KEY][$id])) {
            unset($_SESSION[self::SESSION_KEY][$id]);
            return true;
        }
        return false;
    }

    public function reorder(array $orders): void
    {
        foreach ($orders as $id => $display) {
            if (isset($_SESSION[self::SESSION_KEY][$id])) {
                $_SESSION[self::SESSION_KEY][$id]['display_order'] = (int)$display;
            }
        }
    }
}
