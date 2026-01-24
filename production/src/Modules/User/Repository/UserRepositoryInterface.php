<?php
namespace Modules\User\Repository;

use Modules\User\Domain\User;

interface UserRepositoryInterface
{
    /** @return User[] */
    public function all(): array;

    public function find(int $id): ?User;

    public function save(User $user): User;

    public function delete(int $id): bool;

    /**
     * Reorder a user's achievements. Accepts map of achievement_id => display_order.
     * @param int $userId
     * @param array<int,int> $orders
     */
    public function reorderAchievements(int $userId, array $orders): void;

    /**
     * Get a user's assigned achievements map (achievement_id => display_order)
     * @param int $userId
     * @return array<int,int>
     */
    public function getUserAchievements(int $userId): array;

    /**
     * Assign an achievement to a user with an optional display order.
     */
    public function addAchievement(int $userId, int $achievementId, int $displayOrder = 0): void;

    /**
     * Remove an assigned achievement from a user.
     */
    public function removeAchievement(int $userId, int $achievementId): void;
}
