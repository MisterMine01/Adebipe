<?php

use Adebipe\Cli\Includer\Includer;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/services/Cli/Tests.php';

$includer = new Includer();

$includer->includeAllFile(dirname(__DIR__) . '/services');

mkdir(dirname(__DIR__) . '/tmp', 0777, true);
