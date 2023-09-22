<?php
/**
 * Router of the application in production
 */
$time_start = microtime(true);

//putenv('ENV=dev'); // For test the prod router

chdir(__DIR__);
try {
    include_once __DIR__ . '/services.php';

    $request = new \Adebipe\Router\Request(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        getallheaders(),
        file_get_contents('php://input'),
        $_GET,
        $_FILES,
        $_COOKIE,
        $_POST,
        $_SERVER['SERVER_PORT'],
        $_SERVER['REMOTE_ADDR']
    );

    $router = getAdebipe_Services_Router();
    $injector = getAdebipe_Services_Injector();

    $router->getResponse($request, $injector)->send();
} catch (\Throwable $e) {
    $logger = getAdebipe_Services_Logger();
    $logger->error($e->getMessage());
    $logger->error($e->getTraceAsString());
    $Response = new \Adebipe\Router\Response("Internal server error", 500);
    $Response->send();
}

$logger = getAdebipe_Services_Logger();
$time_end = microtime(true);
$logger->info('Execution time: ' . ($time_end - $time_start) * 1000 . 'ms');
atEnd();
