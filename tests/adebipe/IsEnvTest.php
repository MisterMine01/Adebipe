<?php

use Adebipe\Services\Settings;
use PHPUnit\Framework\TestCase;

class IsEnvTest extends TestCase
{
    public function testIsEnv()
    {
        $this->assertEquals('tests', Settings::getEnvVariable('ENV'));
    }
}