<?php

namespace Tests\Adebipe\Services\Core\Utils;

use Adebipe\Services\Injector;
use Adebipe\Services\Logger;
use PHPUnit\Framework\TestCase;

class InjectorTest extends TestCase
{
    public function testInjector()
    {
        $injector = new Injector(new Logger());
        $this->assertNull($injector->getService(Logger::class));
        $injector->addService(new Logger());
        $this->assertNotNull($injector->getService(Logger::class));
        $this->assertInstanceOf(Logger::class, $injector->getService(Logger::class));
    }
}
