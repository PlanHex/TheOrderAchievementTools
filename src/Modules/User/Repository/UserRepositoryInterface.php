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
}
