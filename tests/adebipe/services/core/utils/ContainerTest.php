<?php

use Adebipe\Cli\MakeClasses;
use Adebipe\Services\Container;
use Adebipe\Services\Injector;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Logger;
use PHPUnit\Framework\AdebipeCoreTestCase;

class ContainerTest extends AdebipeCoreTestCase
{
    private Container $container;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->container = MakeClasses::$container;
    }

    public function testAddGetServices()
    {
        $container = new Container();
        $container->addService(new Logger());
        $this->assertInstanceOf(Logger::class, $container->getService(Logger::class));
        $container->addService(new Container());
        $allServices = $container->getServices();
        $this->assertArrayHasKey(Logger::class, $allServices);
        $this->assertArrayHasKey(Container::class, $allServices);
    }

    public function testGetInvalidService()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Service not found");
        $this->container->getService("InvalidService");
    }

    public function testGetReflection()
    {
        $reflection = $this->container->getReflection(Logger::class);
        $this->assertInstanceOf(ReflectionClass::class, $reflection);
        $this->assertEquals(Logger::class, $reflection->getName());
    }


    public function testGetReflections()
    {
        $container = new Container();
        $reflection = new ReflectionClass(Logger::class);
        $container->addReflection($reflection);
        $allReflections = $container->getReflections();
        $this->assertArrayHasKey(Logger::class, $allReflections);
    }

    public function testAddGetReflection()
    {
        $container = new Container();
        $reflection = new ReflectionClass(Logger::class);
        $container->addReflection($reflection);
        $this->assertEquals($reflection, $this->container->getReflection(Logger::class));
    }

    public function testCreateClass()
    {
        $container = new Container();
        $injector = new Injector($this->container->getService(Logger::class));
        $container->addService($injector);

        $reflection = new ReflectionClass(Logger::class);
        $container->addReflection($reflection);

        $logger = $container->getService(Logger::class);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateClassWithoutInjector()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Service not found");
        $container = new Container();
        $reflection = new ReflectionClass(Logger::class);
        $container->addReflection($reflection);
        $container->getService(Logger::class);
    }

    public function testCreateClassWithInvalidInjector()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The injector is not an instance of Injector");
        $container = new Container();
        setProperty($container, "_services", [Injector::class => new FalseInjector()]);

        $reflection = new ReflectionClass(Logger::class);
        $container->addReflection($reflection);
        $container->getService(Logger::class);
    }

    public function testGetSubclassInterfaces()
    {
        $result = $this->container->getSubclassInterfaces(RegisterServiceInterface::class);
        $this->assertIsArray($result);
        $this->assertTrue(count($result) > 4); // There are 5 classes that implement RegisterServiceInterface when the test was written
        foreach ($result as $interface) {
            $this->assertTrue(in_array(RegisterServiceInterface::class, class_implements($interface)));
        }
    }
}
