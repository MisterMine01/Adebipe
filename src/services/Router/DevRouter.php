<?php

namespace Api\Services;

use Api\Router\Annotations\Route;
use Api\Router\Request;
use Api\Router\Response;
use Api\Services\Interfaces\BuilderServiceInterface;

class Router implements BuilderServiceInterface
{
    /**
     * All routes of the application
     * [path => [method => function]]
     * @var array
     */
    private array $routes = [];

    /**
     * Logger
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * build the prod router
     * @param string $classCode
     * @return string The prod router
     */
    public function build(string $classCode): ?string
    {
        $this->updateRoutes();
        include_once __DIR__ . '/RouterBuild';
        return (new \RouterBuild($this->routes))->getBuilderRouter();
    }

    /**
     * Update the routes
     */
    private function updateRoutes(): void
    {
        $this->routes = [];
        foreach (get_declared_classes() as $class) {
            if (preg_match('/^Api\\\\Components\\\\/', $class) === 0) {
                // Don't check the class if it's not a component
                continue;
            }
            $reflection = new \ReflectionClass($class);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            // Check all public methods
            foreach ($methods as $method) {
                if (!$method->isStatic()) {
                    throw new \Exception('Method ' . $method->getName() . ' in class ' . $class . ' is not static');
                }
                $attributes = $method->getAttributes(Route::class);
                // Check if the method has a route
                if (count($attributes) === 0) {
                    throw new \Exception('Method ' . $method->getName() . ' in class ' . $class . ' has no route');
                }
                $route = $attributes[0]->newInstance();
                // Check if the route already exists
                if (isset($this->routes[$route->path])) {
                    if (isset($this->routes[$route->path][$route->method])) {
                        throw new \Exception('Route ' . $route->path . ' already exists');
                    }
                    $this->routes[$route->path] = [];
                }
                // Add the route
                $this->routes[$route->path][$route->method] = $method;
            }
        }
    }

    /**
     * Get the response for a request
     * @param Request $request
     * @param Injector $injector
     * @return Response
     */
    public function getResponse(Request $request, Injector $injector): Response
    {
        // Remove double slashes or more in the uri
        $request->uri = preg_replace('(\/+)', '/', $request->uri);

        if (is_file("public" . $request->uri)) {
            // The request is a file
            $this->logger->info('Get file: ' . $request->uri);
            $mime = json_decode(file_get_contents(__DIR__ . '/mime.json'), true);
            return new Response(file_get_contents("public" . $request->uri), 200, [
                'Content-Type' => $mime[pathinfo("public" . $request->uri, PATHINFO_EXTENSION)]
            ]);
        }
        // update the routes
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
        // Execute the route with the injector
        $route = $this->routes[$request->uri][$request->method];
        $response = $injector->execute($route, null, [
            Request::class => $request
        ]);
        // Check if the response is an instance of Response
        if (!($response instanceof Response)) {
            throw new \Exception('Response is not an instance of Response');
        }
        return $response;
    }    
}