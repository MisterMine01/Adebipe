<?php

use Adebipe\Cli\MakeClasses;
use Adebipe\Services\Logger;
use Adebipe\Services\Settings;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
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
    }
}
