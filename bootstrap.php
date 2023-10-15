<?php

use Adebipe\Cli\Includer\Includer;

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/services/Cli/Tests.php';

$includer = new Includer();

$includer->includeAllFile(__DIR__ . '/services');
