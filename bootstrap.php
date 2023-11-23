<?php

use Adebipe\Cli\Includer\Includer;
use Adebipe\Cli\MakeClasses;
use Adebipe\Services\ConfigRunner;
use Adebipe\Services\Settings;

putenv('ENV=test');

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/tests/abstract/AdebipeCoreTestCase.php';

require __DIR__ . '/services/Cli/Tests.php';

$includer = new Includer();

$data = $includer->includeAllFile(__DIR__ . '/services');

$config_runner = new ConfigRunner();
$data2 = $includer->includeAllFile(__DIR__ . '/' . Settings::getConfig('DIR'));

MakeClasses::makeClasses(array_merge($data, $data2), $config_runner);
