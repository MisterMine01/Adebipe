<?php

namespace Tests\Adebipe\Services\Core\Utils;

use Adebipe\Services\Logger;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

class LoggerTest extends TestCase
{
    public function testLogger()
    {
        $logger = new Logger();
        $this->assertTrue(true);
    }
}