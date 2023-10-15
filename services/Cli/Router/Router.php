<?php

namespace Adebipe\Cli\Router;

use Adebipe\Cli\Includer\Includer;
use Adebipe\Cli\MakeClasses;
use Adebipe\Router\Request;
use Adebipe\Services\ConfigRunner;
use Adebipe\Services\Logger;
use Adebipe\Services\Router as ServicesRouter;
use Adebipe\Services\Settings;

/**
 * Router of the CLI for development environment.
 * More information about the router in the README.md
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Router
{
    private float $_time_start = 0;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->_time_start = microtime(true);
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

    /**
     * Run the router
     *
     * @param string $cwd The current working directory
     *
     * @return void
     */
    public function run($cwd = __DIR__): void
    {
        chdir($cwd);
        $includer = new Includer();
        $data = $includer->includeAllFile($cwd . '/services');
        $config_runner = new ConfigRunner();
        $data2 = $includer->includeAllFile($cwd . '/' . Settings::getConfig('DIR'));

        MakeClasses::makeClasses(array_merge($data, $data2), $config_runner);
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
            'Time: ' . ($time_end - $this->_time_start) * 1000 . 'ms'
        );

        MakeClasses::stopServices();
    }
}
