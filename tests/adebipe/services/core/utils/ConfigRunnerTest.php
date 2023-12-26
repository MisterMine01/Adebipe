<?php

use Adebipe\Cli\MakeClasses;
use Adebipe\Services\ConfigRunner;
use Adebipe\Services\Logger;
use Adebipe\Services\Settings;
use PHPUnit\Framework\AdebipeCoreTestCase;

class ConfigRunnerTest extends AdebipeCoreTestCase
{
    public function testIfIsEnvFileExists()
    {
        $configRunner = new ConfigRunner();
        $this->assertTrue(Settings::getEnvVariable('_TEST_ENV') === 'test');
        $this->assertTrue(Settings::getEnvVariable('_TEST_ENV_LOCAL') === 'test');
        $this->assertTrue(Settings::getEnvVariable('_TEST_ENV_TEST') === 'test');
        $this->assertTrue(Settings::getEnvVariable('_TEST_ENV_TEST_LOCAL') === 'test');
        $this->assertTrue(Settings::getEnvVariable('_CONFIG_TEST') === 'test');
    }

    public function testNoEnv()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The environment variable ENV is not set');
        putenv('ENV');
        $configRunner = new ConfigRunner();
    }

    public function testLoggerInfo()
    {
        $configRunner = new ConfigRunner();
        $logger = MakeClasses::$container->getService(Logger::class);

        $configRunner->atStart($logger);
        $this->assertMatchesRegularExpression("/Initialize the environment variables/", $logger->logTrace[count($logger->logTrace) - 1]);
        $configRunner->atEnd();
    }

    public function testLoggerError()
    {
        $configRunner = new ConfigRunner();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Logger service not found');
        $configRunner->atStart();
    }

    public function testCommentWork()
    {
        $configRunner = new ConfigRunner();
        $this->assertNull(Settings::getEnvVariable("ENVTEST"));
    }

    public function testException()
    {
        $configRunner = new ConfigRunner();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches("/^Invalid line in the file .+: .+$/");
        invokeMethod($configRunner, '_readEnvFile', ['tests/other/test.env']);
    }
}
