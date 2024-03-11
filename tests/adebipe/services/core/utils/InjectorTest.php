<?php

use Adebipe\Cli\MakeClasses;
use Adebipe\Services\Injector;
use Adebipe\Services\Logger;
use Adebipe\Services\Settings;
use PHPUnit\Framework\AdebipeCoreTestCase;

class InjectorTest extends AdebipeCoreTestCase
{
    private Injector $_injector;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->_injector = MakeClasses::$injector;
    }

    public function testAddGetService()
    {
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 0);
        $logger = new Logger();
        Settings::addConfig("CORE.LOGGER.LOG_LEVEL", 1);
        $loggerProperty = getProperty($logger, "_loglevel");
        setProperty($logger, "_loglevel", 0);
        $injector = new Injector($logger);
        $injector->addService(new FalseRegisterClass());
        $this->assertMatchesRegularExpression("/\: Add service\: ..*$/", $logger->logTrace[count($logger->logTrace) - 1]);
        $this->assertInstanceOf(FalseRegisterClass::class, $injector->getService(FalseRegisterClass::class));
        setProperty($logger, "_loglevel", $loggerProperty);
    }
}
