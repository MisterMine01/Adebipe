<?php

namespace App\Components;

use Adebipe\Components\Interfaces\ComponentInterface;
use Adebipe\Router\Annotations\Route;
use Adebipe\Router\Response;
use Adebipe\Services\Renderer;
use Adebipe\Services\RouteKeeper as RouteKeeperAlias;

class RouteComponents implements ComponentInterface
{
    #[Route(path: '/adebipe/routes', method: 'GET')]
    public static function index(Renderer $renderer, RouteKeeperAlias $routeKeeper): Response
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
                        $route['env'] = $attribute->getArguments()['env'];
                        continue;
                    }
                    $route['more'][$attribute->getName()] = $attribute->getArguments();
                }
                $routes_after[] = $route;
            }
        }
        return $renderer->render(
            'Adebipe/routes/routes.php',
            [
            'routes' => $routes_after,
            ]
        );
    }
}
