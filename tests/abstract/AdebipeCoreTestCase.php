<?php

namespace PHPUnit\Framework;

use Adebipe\Services\ConfigRunner;

abstract class AdebipeCoreTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $runner = new ConfigRunner();
    }
}
