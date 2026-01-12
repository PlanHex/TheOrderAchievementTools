<?php
namespace Infrastructure\Persistence\MySQL;

use Core\Database;
use Modules\User\Domain\User;
use Modules\User\Repository\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /** @return User[] */
    public function all(): array
    {
        $rows = $this->db->fetchAll('SELECT * FROM users ORDER BY id ASC');
        $out = [];
        foreach ($rows as $r) {
            $out[] = new User((int)$r['id'], $r['name'], $r['created_at'] ?? null);
        }
        return $out;
    }

    public function find(int $id): ?User
    {
        $r = $this->db->fetch('SELECT * FROM users WHERE id = :id', ['id' => $id]);
        if (!$r) return null;
        return new User((int)$r['id'], $r['name'], $r['created_at'] ?? null);
    }

    public function save(User $user): User
    {
        if ($user->id === null) {
            $this->db->execute('INSERT INTO users (name) VALUES (:name)', ['name' => $user->name]);
            $user->id = (int)$this->db->pdo()->lastInsertId();
            return $user;
        }

        $this->db->execute('UPDATE users SET name = :name WHERE id = :id', ['name' => $user->name, 'id' => $user->id]);
        return $user;
    }

    public function delete(int $id): bool
    {
        return (bool)$this->db->execute('DELETE FROM users WHERE id = :id', ['id' => $id]);
    }

    public function reorderAchievements(int $userId, array $orders): void
    {
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('INSERT INTO user_achievements (user_id, achievement_id, display_order) VALUES (:uid, :aid, :display) ON DUPLICATE KEY UPDATE display_order = :display');
        foreach ($orders as $aid => $display) {
            $stmt->execute(['uid' => $userId, 'aid' => (int)$aid, 'display' => (int)$display]);
        }
    }

    /**
     * Helper: returns map achievement_id => display_order for the user
     * @return array<int,int>
     */
    public function getUserAchievements(int $userId): array
    {
        $rows = $this->db->fetchAll('SELECT achievement_id, display_order FROM user_achievements WHERE user_id = :uid ORDER BY display_order ASC', ['uid' => $userId]);
        $out = [];
        foreach ($rows as $r) {
            $out[(int)$r['achievement_id']] = (int)$r['display_order'];
        }
        return $out;
    }
}
