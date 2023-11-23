<?php

use Adebipe\Cli\MakeClasses;
use Adebipe\Services\Logger;
use Adebipe\Services\Settings;
use PHPUnit\Framework\AdebipeCoreTestCase;
use PHPUnit\Framework\TestCase;

class LoggerTest extends AdebipeCoreTestCase
{
    private $logger;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->logger = MakeClasses::$container->getService(Logger::class);
    }

    public function testIsLogLevel()
    {
        $this->assertEquals("INFO", $this->logger->getLogLevels());
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 2);
        $this->assertEquals("INFO", $this->logger->getLogLevels());
    }

    public function testIfLogFolderExists()
    {
        $logger = new Logger();
        $isFolder = is_dir("logs");
        $this->assertTrue($isFolder);

        Settings::addConfig("CORE.LOGGER.LOG_FOLDER", "test_logs");
        $logger = new Logger();
        $isFolder = is_dir("test_logs");
        $this->assertTrue($isFolder);
        rmdir("test_logs");

        Settings::addConfig("CORE.LOGGER.LOG_FOLDER", null);
        $logger = new Logger();
        $isFolder = is_dir("logs");
        $this->assertTrue($isFolder);
    }

    public function testNoLogLevel()
    {
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", null);
        $logger = new Logger();
        $this->assertEquals("INFO", $logger->getLogLevels());
    }

    public function testLogLevelNeg()
    {
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", -1);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid log level");
        $logger = new Logger();
    }

    public function testLogLevelPos()
    {
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 5);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid log level");
        $logger = new Logger();
    }

    public function testLogLevelZero()
    {
        $this->expectNotToPerformAssertions();
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 0);
        $logger = new Logger();
    }


    public function testLogLevelFour()
    {
        $this->expectNotToPerformAssertions();
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 4);
        $logger = new Logger();
    }
}
