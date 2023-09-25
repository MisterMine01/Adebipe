<?php

namespace Tests\Adebipe\Services\Core\Utils;

use Adebipe\Services\Logger;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

class LoggerTest extends TestCase
{
    public function testLogLevels()
    {
        $logger = new Logger();
        $this->assertEquals("INFO", $logger->getLogLevels());
        putenv('LOG_LEVEL=1');
        $logger = new Logger();
        $this->assertEquals("INFO", $logger->getLogLevels());
        putenv('LOG_LEVEL=2');
        $this->assertEquals("INFO", $logger->getLogLevels());
        putenv('LOG_LEVEL=3');
        $logger2 = new Logger();
        $this->assertEquals("ERROR", $logger2->getLogLevels());
        $this->assertEquals("INFO", $logger->getLogLevels());
        putenv('LOG_LEVEL');
    }

    public function testLogLevelsMax()
    {
        putenv('LOG_LEVEL=5');
        $this->expectException(\Exception::class);
        $logger4 = new Logger();
        putenv('LOG_LEVEL');
    }

    public function testLogLevelsMin()
    {
        putenv('LOG_LEVEL=-1');
        $this->expectException(\Exception::class);
        $logger3 = new Logger();
        putenv('LOG_LEVEL');
    }
}