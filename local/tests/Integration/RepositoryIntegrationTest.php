<?php
use PHPUnit\Framework\TestCase;

class RepositoryIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        // seed demo session data (silent)
        require __DIR__ . '/../../scripts/seed_demo.php';
    }

    public function testRepositoriesHaveData()
    {
        $c = new \Core\Container();
        $catRepo = $c->get('category_repository');
        $achRepo = $c->get('achievement_repository');
        $userRepo = $c->get('user_repository');

        $this->assertGreaterThan(0, count($catRepo->all()));
        $this->assertGreaterThan(0, count($achRepo->all()));
        $this->assertGreaterThan(0, count($userRepo->all()));
    }

    public function testUserAchievementMethods()
    {
        $c = new \Core\Container();
        $userRepo = $c->get('user_repository');

        // pick an existing seeded user
        $userId = 1;
        $map = $userRepo->getUserAchievements($userId);
        $this->assertIsArray($map);

        // add a synthetic achievement id for testing (safe in InMemory mode)
        $testAid = 99999;
        $userRepo->addAchievement($userId, $testAid, 123);
        $map = $userRepo->getUserAchievements($userId);
        $this->assertArrayHasKey($testAid, $map);
        $this->assertEquals(123, $map[$testAid]);

        // reorder some values
        $userRepo->reorderAchievements($userId, [$testAid => 5, 4 => 10]);
        $map = $userRepo->getUserAchievements($userId);
        $this->assertEquals(5, $map[$testAid]);

        // remove the synthetic achievement
        $userRepo->removeAchievement($userId, $testAid);
        $map = $userRepo->getUserAchievements($userId);
        $this->assertArrayNotHasKey($testAid, $map);
    }
}
