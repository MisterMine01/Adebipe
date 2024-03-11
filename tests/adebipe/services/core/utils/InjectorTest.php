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
        $logger = new Logger();
        $loggerProperty = getProperty($logger, "_loglevel");
        setProperty($logger, "_loglevel", 0);
        $injector = new Injector($logger);
        $injector->addService(FalseRegisterClass::getClass());
        $this->assertMatchesRegularExpression("/\: Add service\: ..*$/", $logger->logTrace[count($logger->logTrace) - 1]);
        $this->assertInstanceOf(FalseRegisterClass::class, $injector->getService(FalseRegisterClass::class));
        setProperty($logger, "_loglevel", $loggerProperty);
    }

    public function testInjectParamsNoType()
    {
        $this->expectException(Exception::class);
        // 'Param ' . $param_name . ' in method ' . $method->getName() . ' in class ' . $method->getDeclaringClass()->getName() . ' has no type'
        $this->expectExceptionMessageMatches("/^Param .+ in method .+ in class .+ has no type$/");
        $method = new ReflectionMethod(FalseRegisterClass::class, "noType");
        invokeMethod($this->_injector, "_injectParams", [$method, []]); // $method->getParameters() is empty
    }

    public function testTypeInjection()
    {
        $expected = [1, "name", null, "default"];

        $method = new ReflectionMethod(FalseRegisterClass::class, "otherType");

        $params = invokeMethod($this->_injector, "_injectParams", [$method, ["int" => 1, "fromName" => "name"]]);
        $this->assertEquals($expected, $params);
    }

    public function testNoFoundValue()
    {
        $this->expectException(Exception::class);
        # 'Param ' . $param_name . ' in method ' . $method->getName() . ' in class ' . $method->getDeclaringClass()->getName() . ' can\'t be injected'
        $this->expectExceptionMessageMatches("/^Param .+ in method .+ in class .+ can't be injected$/");
        $method = new ReflectionMethod(FalseRegisterClass::class, "otherType");
        invokeMethod($this->_injector, "_injectParams", [$method, []]);
    }

    public function testExecuteMethod()
    {
        $method = new ReflectionMethod(FalseRegisterClass::class, "otherType");
        $object = FalseRegisterClass::getClass();
        $result = $this->_injector->execute($method, $object, ["int" => 1, "fromName" => "name"]);
        $this->assertEquals("testOtherType", $result);
    }

    public function testCreateClassNoPublic()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches("/^Constructor of class .+ is not public$/");
        $this->_injector->createClass(new ReflectionClass(FalseRegisterClass::class));
    }

    public function testCreateClassNoConstructor()
    {
        $logger = new Logger();
        $loggerProperty = getProperty($logger, "_loglevel");
        setProperty($logger, "_loglevel", 0);
        $injector = new Injector($logger);
        $injector->createClass(new ReflectionClass(FalseInjector::class));
        $this->assertMatchesRegularExpression("/\: Class .+ has no constructor$/", $logger->logTrace[count($logger->logTrace) - 1]);
        setProperty($logger, "_loglevel", $loggerProperty);
    }
}
