<?php

namespace Api\Cli;

use Api\Router\Request;
use Api\Services\Logger;
use Api\Services\Router as ServicesRouter;

include_once __DIR__ . '/Includer.php';
include_once __DIR__ . '/MakeClasses.php';

class Router
{
    private float $time_start = 0;

    public function __construct()
    {
        $this->time_start = microtime(true);
        if (!defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        if (!defined('STDERR')) {
            define('STDERR', fopen('php://stderr', 'w'));
        }

        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
    }

    public function run($cwd = __DIR__): void
    {
        $includer = new Includer();
        $data = $includer->includeList($cwd . '/services');
        $data2 = $includer->includeList($cwd . '/src');

        MakeClasses::makeClasses(array_merge($data, $data2));
        $logger = MakeClasses::$container->getService(Logger::class);
        $logger->info('Router running');

        $request = new Request(
            $_SERVER['REQUEST_METHOD'],
            parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH),
            getallheaders(),
            file_get_contents('php://input'),
            $_GET,
            $_FILES,
            $_COOKIE,
            $_POST,
            $_SERVER['SERVER_PORT'],
            $_SERVER['REMOTE_ADDR']
        );

        $router = MakeClasses::$container->getService(ServicesRouter::class);

        $response = $router->getResponse($request, MakeClasses::$injector);

        $response->send();

        $logger->info('End of router');

        $time_end = microtime(true);

        $logger->info(
            'information about dev Router: ' . PHP_EOL .
            'Peak memory usage: ' . memory_get_peak_usage() / 1024 / 1024 . 'MB' . PHP_EOL .
            'Memory usage: ' . memory_get_usage() / 1024 / 1024 . 'MB' . PHP_EOL .
            'Time: ' . ($time_end - $this->time_start) * 1000 . 'ms'
        );

        MakeClasses::stopServices();
    }
}
