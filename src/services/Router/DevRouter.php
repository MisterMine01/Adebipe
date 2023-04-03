<?php

namespace Api\Services;

use Api\Router\Annotations\Route;
use Api\Router\Request;
use Api\Router\Response;
use Api\Services\Interfaces\BuilderServiceInterface;

class Router implements BuilderServiceInterface
{
    /**
     * @var array
     */
    private array $routes = [];

    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * build the prod router
     */
    public function build(string $classCode): ?string
    {
        $this->updateRoutes();
        include_once __DIR__ . '/RouterBuild';
        return (new \RouterBuild($this->routes))->getBuilderRouter();
    }

    private function updateRoutes(): void
    {
        $this->routes = [];
        foreach (get_declared_classes() as $class) {
            if (preg_match('/^Api\\\\Components\\\\/', $class) === 0) {
                continue;
            }
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (!$method->isStatic()) {
                    throw new \Exception('Method ' . $method->getName() . ' in class ' . $class . ' is not static');
                }
                $attributes = $method->getAttributes(Route::class);
                if (count($attributes) === 0) {
                    throw new \Exception('Method ' . $method->getName() . ' in class ' . $class . ' has no route');
                }
                $route = $attributes[0]->newInstance();
                if (isset($this->routes[$route->path])) {
                    if (isset($this->routes[$route->path][$route->method])) {
                        throw new \Exception('Route ' . $route->path . ' already exists');
                    }
                    $this->routes[$route->path] = [];
                }
                $this->routes[$route->path][$route->method] = $method;
            }
        }
    }

    public function getResponse(Request $request, Injector $injector): Response
    {
        $request->uri = preg_replace('(\/+)', '/', $request->uri);
        if (is_file("public" . $request->uri)) {
            $this->logger->info('Get file: ' . $request->uri);
            return new Response(file_get_contents("public" . $request->uri), 200, [
                'Content-Type' => mime_content_type("public" . $request->uri)
            ]);
        }
        $this->updateRoutes();
        $this->logger->info('Get response for request: ' . $request->uri);
        if (!isset($this->routes[$request->uri])) {
            $this->logger->info('Route not found');
            return new Response('Not found', 404);
        }
        if (!isset($this->routes[$request->uri][$request->method])) {
            $this->logger->info('Method not allowed');
            return new Response('Method not allowed', 405);
        }
        $route = $this->routes[$request->uri][$request->method];
        $response = $injector->execute($route, null, [
            Request::class => $request
        ]);
        if (!($response instanceof Response)) {
            throw new \Exception('Response is not an instance of Response');
        }
        return $response;

    }    
}