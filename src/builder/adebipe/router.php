<?php
/**
 * Router of the application in production
 */
$time_start = microtime(true);

//putenv('ENV=dev'); // For test the prod router

try {
    include_once __DIR__ . '/services/loader.php';

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

    $Router->getResponse($request, $Injector)->send();
} catch (\Throwable $e) {
    $Logger->error($e->getMessage());
    $Logger->error($e->getTraceAsString());
    $Response = new \Adebipe\Router\Response("Internal server error", 500);
    $Response->send();
}

$time_end = microtime(true);
$Logger->info('Execution time: ' . ($time_end - $time_start) * 1000 . 'ms');
atEnd();
