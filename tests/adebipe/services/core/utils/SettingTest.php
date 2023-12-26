<?php

use Adebipe\Services\Settings;
use PHPUnit\Framework\AdebipeCoreTestCase;

class SettingTest extends AdebipeCoreTestCase
{
    public function testAddEnv()
    {
        $this->assertFalse(getenv("TEST_ENV"));
        Settings::addEnvVariable("TEST_ENV", "test");
        $this->assertEquals("test", getenv("TEST_ENV"));
        putenv("TEST_ENV");
    }

    public function testGetEnv()
    {
        Settings::addEnvVariable("TEST_ENV", "test");
        $this->assertEquals("test", Settings::getEnvVariable("TEST_ENV"));
        $this->assertEquals(null, Settings::getEnvVariable("erghergbvergber"));
        putenv("TEST_ENV");
    }

    public function testAddConfig()
    {
        Settings::addConfigArray([]);
        $this->assertEquals([], Settings::getConfig(null));
        Settings::addConfigArray(["test" => "test"]);
        $this->assertEquals(["test" => "test"], Settings::getConfig(null));
        Settings::addConfigArray(["test2" => "test2"]);
        $this->assertEquals(["test" => "test", "test2" => "test2"], Settings::getConfig(null));
        Settings::addConfigArray(["test3" => "test3"], false);
        $this->assertEquals(["test3" => "test3"], Settings::getConfig(null));
    }

    public function testAddConfigNoKey()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The key must not be empty");
        Settings::addConfig("", "test");
    }
}
