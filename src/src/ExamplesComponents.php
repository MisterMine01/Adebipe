<?php

namespace App\Components;

use App\Components\Interfaces\ComponentInterface;
use Api\Router\Annotations\RegexSimple;
use Api\Router\Annotations\Route;
use Api\Router\Response;
use Api\Services\ORM;
use App\Model\User;

class ExampleComponent implements ComponentInterface
{

    #[Route(path: '/hello', method: 'GET')]
    public static function index(ORM $orm): Response
    {
        $userRepo = $orm->getRepository(User::class);
        $user = new User([
            'username' => 'test',
            'password' => 'test',
            'email' => 'test@test.fr',
        ]);
        $userRepo->save($user);
        return new Response('Hello World');
    }


    #[Route(path: '/{id}', method: 'GET', regex: ['id' => RegexSimple::int])]
    public static function identifier(int $id): Response
    {
        return new Response(strval($id));
    }
}
