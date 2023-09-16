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
    public static function index(Renderer $renderer, RouteKeeper $routeKeeper): Response
    {
        $routes = $routeKeeper->getRoutes();

        $routes_after = [];
        foreach ($routes as $route => $methods) {
            foreach ($methods as $method => $function) {
                $route = [
                    'route_regexed' => $route,
                    'route' => $function[1],
                    'method' => $method,
                    'more' => [],
                ];
                foreach ($function[0]->getAttributes() as $attribute) {
                    if ($attribute->getName() === Route::class) {
                        continue;
                    }
                    $route['more'][$attribute->getName()] = $attribute->getArguments();
                }
                $routes_after[] = $route;
            }
        }
        return $renderer->render('Adebipe/routes/routes.php', [
            'routes' => $routes_after,
        ]);
    }
}