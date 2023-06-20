<?php

namespace App\Components;

use App\Components\Interfaces\ComponentInterface;
use Api\Router\Annotations\RegexSimple;
use Api\Router\Annotations\Route;
use Api\Router\Response;
use App\Model\User;

class ExampleComponent implements ComponentInterface
{

    #[Route(path: '/hello', method: 'GET')]
    public static function index(): Response
    {
        $test = new User();
        return new Response('Hello World');
    }


    #[Route(path: '/{id}', method: 'GET', regex: ['id' => RegexSimple::int])]
    public static function identifier(int $id): Response
    {
        return new Response(strval($id));
    }
}
