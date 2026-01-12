<?php
use PHPUnit\Framework\TestCase;

class CsrfTest extends TestCase
{
    public function testTokenGeneratedAndValidated()
    {
        $t = \Core\Csrf::token();
        $this->assertNotEmpty($t);
        $this->assertTrue(\Core\Csrf::validate($t));
        $this->assertFalse(\Core\Csrf::validate('invalid-token'));
    }
}
