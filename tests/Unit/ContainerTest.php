<?php
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testContainerProvidesRepositories()
    {
        $c = new \Core\Container();
        $this->assertTrue($c->has('category_repository'));
        $this->assertTrue($c->has('achievement_repository'));
        $this->assertTrue($c->has('user_repository'));
        $cat = $c->get('category_repository');
        $this->assertIsObject($cat);
    }
}
