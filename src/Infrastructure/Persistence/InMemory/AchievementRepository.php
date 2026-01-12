<?php
namespace Infrastructure\Persistence\InMemory;

use Modules\Achievement\Domain\Achievement;
use Modules\Achievement\Repository\AchievementRepositoryInterface;

class AchievementRepository implements AchievementRepositoryInterface
{
    private const SESSION_KEY = 'inmemory_achievements';
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
        $rows = $this->loader->load('achievements.csv');
        $out = [];
        foreach ($rows as $r) {
            $id = isset($r['id']) ? (int)$r['id'] : null;
            $out[$id ?? uniqid('a', true)] = [
                'id' => $id,
                'category_id' => isset($r['category_id']) ? (int)$r['category_id'] : 0,
                'title' => $r['title'] ?? '',
                'description' => $r['description'] ?? null,
                'points' => isset($r['points']) ? (int)$r['points'] : 0,
                'image_url' => $r['image_url'] ?? null,
                'display_order' => isset($r['display_order']) ? (int)$r['display_order'] : 0,
                'created_at' => $r['created_at'] ?? null,
            ];
        }

        $_SESSION[self::SESSION_KEY] = $out;
    }

    /** @return Achievement[] */
    public function all(?int $categoryId = null): array
    {
        $rows = array_values($_SESSION[self::SESSION_KEY] ?? []);
        usort($rows, function ($a, $b) {
            return ($a['display_order'] <=> $b['display_order']) ?: ($a['id'] <=> $b['id']);
        });

        $out = [];
        foreach ($rows as $r) {
            if ($categoryId !== null && (int)$r['category_id'] !== $categoryId) {
                continue;
            }
            $out[] = new Achievement($r['id'], (int)$r['category_id'], $r['title'], $r['description'] ?? null, (int)$r['points'], $r['image_url'] ?? null, (int)$r['display_order'], $r['created_at'] ?? null);
        }

        return $out;
    }

    public function find(int $id): ?Achievement
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        foreach ($rows as $r) {
            if ((int)$r['id'] === $id) {
                return new Achievement($r['id'], (int)$r['category_id'], $r['title'], $r['description'] ?? null, (int)$r['points'], $r['image_url'] ?? null, (int)$r['display_order'], $r['created_at'] ?? null);
            }
        }

        return null;
    }

    public function save(Achievement $achievement): Achievement
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        if ($achievement->id === null) {
            $max = 0;
            foreach ($rows as $r) {
                $max = max($max, (int)$r['id']);
            }
            $achievement->id = $max + 1;
        }

        $_SESSION[self::SESSION_KEY][$achievement->id] = [
            'id' => $achievement->id,
            'category_id' => $achievement->categoryId,
            'title' => $achievement->title,
            'description' => $achievement->description,
            'points' => $achievement->points,
            'image_url' => $achievement->imageUrl,
            'display_order' => $achievement->displayOrder,
            'created_at' => $achievement->createdAt,
        ];

        return $achievement;
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
