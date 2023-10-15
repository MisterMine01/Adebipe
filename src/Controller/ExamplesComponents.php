<?php

namespace App\Components;

use Adebipe\Annotations\ValidatePost;
use Adebipe\Components\Interfaces\ComponentInterface;
use Adebipe\Router\Annotations\RegexSimple;
use Adebipe\Router\Annotations\Route;
use Adebipe\Router\Request;
use Adebipe\Router\Response;
use Adebipe\Services\ORM;
use Adebipe\Services\Renderer;
use App\Model\User;

class ExampleComponent implements ComponentInterface
{
    #[Route(path: '/hello', method: 'GET')]
    public static function index(ORM $orm): Response
    {
        $userRepo = $orm->getRepository(User::class);
        $user = new User(
            [
            'username' => 'test',
            'password' => 'test',
            'email' => 'test@test.fr',
            ]
        );
        $userRepo->save($user);
        return new Response('Hello World');
    }


    #[Route(path: '/{id}', method: 'GET', regex: ['id' => RegexSimple::int])]
    public static function identifier(int $id): Response
    {
        return new Response(strval($id));
    }

    #[Route(path: '/test', method: 'POST')]
    #[ValidatePost(
        schema: [
        'username' => 'string',
        'password' => 'string',
        'email' => '?string',
        ]
    )]
    public static function render(Renderer $renderer, Request $request): Response
    {
        return $renderer->render('test.php', ['post' => $request->post]);
    }
}
