<?php
namespace Infrastructure\Persistence\InMemory;

use Modules\User\Domain\User;
use Modules\User\Repository\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private const SESSION_KEY = 'inmemory_users';
    private const SESSION_UA_KEY = 'inmemory_user_achievements';
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
        $rows = $this->loader->load('users.csv');
        $out = [];
        foreach ($rows as $r) {
            $id = isset($r['id']) ? (int)$r['id'] : null;
            $out[$id ?? uniqid('u', true)] = [
                'id' => $id,
                'name' => $r['name'] ?? '',
                'created_at' => $r['created_at'] ?? null,
            ];
        }

        $_SESSION[self::SESSION_KEY] = $out;

        // seed user_achievements
        $ua = $this->loader->load('user_achievements.csv');
        $map = [];
        foreach ($ua as $r) {
            $uid = isset($r['user_id']) ? (int)$r['user_id'] : null;
            $aid = isset($r['achievement_id']) ? (int)$r['achievement_id'] : null;
            $display = isset($r['display_order']) ? (int)$r['display_order'] : 0;
            if ($uid === null || $aid === null) continue;
            $map[$uid][$aid] = $display;
        }

        $_SESSION[self::SESSION_UA_KEY] = $map;
    }

    /** @return User[] */
    public function all(): array
    {
        $rows = array_values($_SESSION[self::SESSION_KEY] ?? []);
        $out = [];
        foreach ($rows as $r) {
            $out[] = new User($r['id'], $r['name'], $r['created_at'] ?? null);
        }
        return $out;
    }

    public function find(int $id): ?User
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        foreach ($rows as $r) {
            if ((int)$r['id'] === $id) {
                return new User($r['id'], $r['name'], $r['created_at'] ?? null);
            }
        }
        return null;
    }

    public function save(User $user): User
    {
        $rows = $_SESSION[self::SESSION_KEY] ?? [];
        if ($user->id === null) {
            $max = 0;
            foreach ($rows as $r) {
                $max = max($max, (int)$r['id']);
            }
            $user->id = $max + 1;
        }

        $_SESSION[self::SESSION_KEY][$user->id] = [
            'id' => $user->id,
            'name' => $user->name,
            'created_at' => $user->createdAt,
        ];

        return $user;
    }

    public function delete(int $id): bool
    {
        if (isset($_SESSION[self::SESSION_KEY][$id])) {
            unset($_SESSION[self::SESSION_KEY][$id]);
            // also remove user achievements
            if (isset($_SESSION[self::SESSION_UA_KEY][$id])) {
                unset($_SESSION[self::SESSION_UA_KEY][$id]);
            }
            return true;
        }
        return false;
    }

    public function reorderAchievements(int $userId, array $orders): void
    {
        if (!isset($_SESSION[self::SESSION_UA_KEY][$userId])) {
            $_SESSION[self::SESSION_UA_KEY][$userId] = [];
        }

        foreach ($orders as $achievementId => $display) {
            $_SESSION[self::SESSION_UA_KEY][$userId][(int)$achievementId] = (int)$display;
        }
    }

    /**
     * Helper: get assigned achievements map for a user
     * @return array<int,int> achievement_id => display_order
     */
    public function getUserAchievements(int $userId): array
    {
        return $_SESSION[self::SESSION_UA_KEY][$userId] ?? [];
    }
}
