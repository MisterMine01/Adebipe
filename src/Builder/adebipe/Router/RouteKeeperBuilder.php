<?php

use Adebipe\Router\Annotations\Route;
use Adebipe\Services\RouteKeeper;
use Adebipe\Services\Router;

class RouteKeeperBuilder implements BuilderInterface
{
    public function includeFiles(): array
    {
        return [];
    }

    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {
        $controller_class = $core->includeFolder('src/Controller');
        $file_code = file_get_contents(__DIR__ . '/RouteKeeper.php');
        $route_keeper = $core->getService(RouteKeeper::class);
        $route_keeper->updateRoutes();

        $routes = $route_keeper->getRoutes();
        /*
        $reflections = [];
        foreach ($controller_class as $class) {
            $reflections[] = new ReflectionClass($class);
        }
        $functions = [];
        foreach ($reflections as $reflection) {
            $methods = $reflection->getMethods(ReflectionMethod::IS_STATIC);
            foreach ($methods as $method) {
                if (!$method->getAttributes(Route::class)) {
                    continue;
                }
                $route = $method->getAttributes(Route::class)[0]->newInstance();
                $route

            }
        }*/
    }
}