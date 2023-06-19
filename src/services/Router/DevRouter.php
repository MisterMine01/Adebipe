<?php

namespace Api\Services;

use Api\Router\Annotations\RegexSimple;
use Api\Router\Annotations\Route;
use Api\Router\Request;
use Api\Router\Response;
use Api\Services\Interfaces\BuilderServiceInterface;

class Router implements BuilderServiceInterface
{
    /**
     * All routes of the application
     * [path => [
     *      method => [
     *          function,
     *          route    
     *      ]
     * ]]
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
        return (new \RouterBuild($this->logger, $this->routes))->getBuilderRouter();
    }

    /**
     * Update the routes
     */
    private function updateRoutes(): void
    {
        $this->routes = [];
        foreach (get_declared_classes() as $class) {
            if (preg_match('/^App\\\\Components\\\\/', $class) === 0) {
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
                $regex_decoded = $route->path;
                if ($route->regex !== null) {
                    foreach ($route->regex as $param => $regex) {
                        if ($regex::class == RegexSimple::class) {
                            $regex = $regex->value;
                        }
                        $regex_decoded = str_replace('{' . $param . '}', '(' . $regex . ')', $regex_decoded);
                        $regex_decoded = str_replace('/', '\/', $regex_decoded);
                        $regex_decoded = "/^" . $regex_decoded . "$/";
                    }
                }
                // Add the route
                $this->routes[$regex_decoded][$route->method] = [$method, $route->path];
            }
        }
    }

    /**
     * Get the the id of the route and assign is value from the uri
     */
    public function perform_regex(string $route, string $regex_route, string $uri): array
    {
        $this->logger->info('Perform regex for route: ' . $route);

        $to_inject = [];
        
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $matches);
        $id = $matches[0];
        $this->logger->info('Get id: ' . json_encode($id));
        preg_match($regex_route, $uri, $matches);
        $this->logger->info('Get matches: ' . json_encode($matches));

        for ($i = 0; $i < count($id); $i++) {
            $id_sub = substr($id[$i], 1, -1);
            $to_inject[$id_sub] = $matches[$i + 1];
        }
        $this->logger->info('Get to inject: ' . json_encode($to_inject));
        return $to_inject;
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
        $add_to_injector = [
            Request::class => $request,
        ];
        $route = null;
        if (isset($this->routes[$request->uri])) {
            $route = $request->uri;
        } else {
            // Check if the route is a regex
            foreach ($this->routes as $key => $value) {
                if (preg_match('/^\/\^.*\$\/$/', $key) === 0) {
                    continue;
                }
                if (preg_match($key, $request->uri)) {
                    $this->logger->info('Regex match');
                    if (!isset($this->routes[$key][$request->method])) {
                        $this->logger->info('Method not allowed');
                        return new Response('Method not allowed', 405);
                    }
                    $regex = $this->perform_regex($value[$request->method][1], $key, $request->uri);
                    $route = $key;
                    foreach ($regex as $key => $value) {
                        $add_to_injector[$key] = $value;
                    }
                    break;
                }
            }
            if ($route === null) {
                $this->logger->info('Route not found');
                return new Response('Not found', 404);
            }
        }
        $this->logger->info('Route found');
        $this->logger->info('Route: ' . json_encode($route));
        if (!isset($this->routes[$route][$request->method])) {
            $this->logger->info('Method not allowed');
            return new Response('Method not allowed', 405);
        }
        // Execute the route with the injector
        $route = $this->routes[$route][$request->method][0];
        $response = $injector->execute($route, null, $add_to_injector);
        // Check if the response is an instance of Response
        if (!($response instanceof Response)) {
            throw new \Exception('Response is not an instance of Response');
        }
        return $response;
    }
}
