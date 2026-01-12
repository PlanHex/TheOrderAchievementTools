<?php
namespace Infrastructure\Persistence\MySQL;

use Core\Database;
use Modules\Achievement\Domain\Achievement;
use Modules\Achievement\Repository\AchievementRepositoryInterface;

class AchievementRepository implements AchievementRepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return Achievement[] */
    public function all(?int $categoryId = null): array
    {
        if ($categoryId === null) {
            $rows = $this->db->fetchAll('SELECT * FROM achievements ORDER BY category_id ASC, display_order ASC, id ASC');
        } else {
            $rows = $this->db->fetchAll('SELECT * FROM achievements WHERE category_id = :cid ORDER BY display_order ASC, id ASC', ['cid' => $categoryId]);
        }

        $out = [];
        foreach ($rows as $r) {
            $out[] = new Achievement((int)$r['id'], (int)$r['category_id'], $r['title'], $r['description'] ?? null, (int)$r['points'], $r['image_url'] ?? null, (int)$r['display_order'], $r['created_at'] ?? null);
        }
        return $out;
    }

    public function find(int $id): ?Achievement
    {
        $r = $this->db->fetch('SELECT * FROM achievements WHERE id = :id', ['id' => $id]);
        if (!$r) return null;
        return new Achievement((int)$r['id'], (int)$r['category_id'], $r['title'], $r['description'] ?? null, (int)$r['points'], $r['image_url'] ?? null, (int)$r['display_order'], $r['created_at'] ?? null);
    }

    public function save(Achievement $achievement): Achievement
    {
        if ($achievement->id === null) {
            $this->db->execute('INSERT INTO achievements (category_id, title, description, points, image_url, display_order) VALUES (:cid, :title, :desc, :points, :img, :display)', [
                'cid' => $achievement->categoryId,
                'title' => $achievement->title,
                'desc' => $achievement->description,
                'points' => $achievement->points,
                'img' => $achievement->imageUrl,
                'display' => $achievement->displayOrder,
            ]);
            $achievement->id = (int)$this->db->pdo()->lastInsertId();
            return $achievement;
        }

        $this->db->execute('UPDATE achievements SET category_id = :cid, title = :title, description = :desc, points = :points, image_url = :img, display_order = :display WHERE id = :id', [
            'cid' => $achievement->categoryId,
            'title' => $achievement->title,
            'desc' => $achievement->description,
            'points' => $achievement->points,
            'img' => $achievement->imageUrl,
            'display' => $achievement->displayOrder,
            'id' => $achievement->id,
        ]);

        return $achievement;
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db->execute('DELETE FROM achievements WHERE id = :id', ['id' => $id]);
    }

    public function reorder(array $orders): void
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('UPDATE achievements SET display_order = :display WHERE id = :id');
        foreach ($orders as $id => $display) {
            $stmt->execute(['display' => (int)$display, 'id' => (int)$id]);
        }
    }
}
