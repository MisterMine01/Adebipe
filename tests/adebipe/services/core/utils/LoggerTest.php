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

    public function testLogStartingInfo()
    {
        $logger = new Logger();
        $this->assertMatchesRegularExpression("/Starting Logger/", $logger->logTrace[0]);
    }

    public function testStdoutLog()
    {
        $logger = new Logger();
        $this->assertIsResource(getProperty($logger, "_logFile"));
        $this->assertEquals(STDOUT, getProperty($logger, "_logFile"));
    }

    public function testFileLog()
    {
        Settings::addConfig("CORE.LOGGER.LOG_IN_FILE", true);
        $logger = new Logger();
        $this->assertIsResource(getProperty($logger, "_logFile"));
        $this->assertNotEquals(STDOUT, getProperty($logger, "_logFile"));
    }

    public function testFileName()
    {
        Settings::addConfig("CORE.LOGGER.LOG_IN_FILE", true);
        $logger = new Logger();
        $this->assertIsResource(getProperty($logger, "_logFile"));
        $fileName = stream_get_meta_data(getProperty($logger, "_logFile"))['uri'];
        $this->assertMatchesRegularExpression("/^logs\/\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}.log$/", $fileName);
    }

    public function testNoSentryConnectionDebug()
    {
        $logger = new Logger();
        $logger->atStart();
        $this->assertDoesNotMatchRegularExpression("/No sentry/", $logger->logTrace[count($logger->logTrace) - 1]);
        $logger->atEnd();
    }

    public function testNoSentryConnection()
    {
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 0);
        $logger = new Logger();
        $logger->atStart();
        $this->assertMatchesRegularExpression("/No sentry/", $logger->logTrace[count($logger->logTrace) - 1]);
        $logger->atEnd();
    }

    public function testNotExistSentryClass()
    {
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 0);
        Settings::addConfig("CORE.LOGGER.ERROR_CLASS", "NotExistentClass");
        $logger = new Logger();
        $logger->atStart();
        $this->assertMatchesRegularExpression("/No sentry/", $logger->logTrace[count($logger->logTrace) - 1]);
        $logger->atEnd();
    }

    // TODO TEST SENTRY REAL CLASS

    public function testEndLogger()
    {
        $array = [];
        $logger = new Logger();
        $logger->atStart();
        $array[1];
        $this->assertMatchesRegularExpression("/Undefined array key 1 in/", $logger->logTrace[count($logger->logTrace) - 1]);
        $logger->atEnd();
        $this->assertMatchesRegularExpression("/Stopping Logger/", $logger->logTrace[count($logger->logTrace) - 1]);
        $array[1];
        $this->assertDoesNotMatchRegularExpression("/Undefined array key 1 in/", $logger->logTrace[count($logger->logTrace) - 1]);
    }
}
