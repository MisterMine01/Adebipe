<?php

namespace Api\Components;

use Api\Components\Interfaces\ComponentInterface;
use Api\Router\Annotations\Route;
use Api\Router\Response;

class ExampleComponent implements ComponentInterface
{

    #[Route(path: '/hello', method: 'GET')]
    public static function index(): Response
    {
        return new Response('Hello World');
    }
}
