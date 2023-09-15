<?php

namespace App\Components;

use Adebipe\Annotations\ValidatePost;
use Adebipe\Components\Interfaces\ComponentInterface;
use Adebipe\Router\Annotations\Route;
use Adebipe\Router\JsonResponse;
use Adebipe\Router\Response;
use Adebipe\Services\Renderer;
use Adebipe\Services\RouteKeeper;

class RouteComponents implements ComponentInterface
{
    #[Route(path: '/adebipe/routes', method: 'GET')]
    #[ValidatePost(schema: [
        "username" => "string",
        "password" => "string",
    ])]
    public static function index(Renderer $renderer, RouteKeeper $routeKeeper): Response
    {
        $routes = $routeKeeper->getRoutes();

        $routes_after = [];
        foreach ($routes as $route => $methods) {
            foreach ($methods as $method => $function) {
                $routes_after[] = [
                    'route_regexed' => $route,
                    'route' => $function[1],
                    'method' => $method,
                    'function' => $function[0]->getName(),
                ];
            }
        }
        return new JsonResponse($routes_after);
    }
}