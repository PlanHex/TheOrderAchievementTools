<?php
use PHPUnit\Framework\TestCase;

class RepositoryIntegrationTest extends TestCase
{
    public function setUp(): void
    {
        // seed demo session data
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
}
