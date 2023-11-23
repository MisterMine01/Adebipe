<?php

namespace PHPUnit\Framework;

use Adebipe\Services\ConfigRunner;

abstract class AdebipeCoreTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $runner = new ConfigRunner();
        if (is_dir("logs_test")) {
            foreach (glob("logs_test/*") as $file) {
                unlink($file);
            }
            rmdir("logs_test");
        }
    }
}
