<?php

namespace Adebipe\Services;

use Adebipe\Router\Annotations\BeforeRoute;
use Adebipe\Router\Request;
use Adebipe\Router\Response;
use Adebipe\Services\Interfaces\BuilderServiceInterface;
use ReflectionMethod;

class Router implements BuilderServiceInterface
{

    private RouteKeeper $routeKeeper;

    /**
     * Logger
     * @var Logger
     */
    private Logger $logger;

    /**
     * Constructor
     * @param Logger $logger
     */
    public function __construct(Logger $logger, RouteKeeper $routeKeeper)
    {
        $this->logger = $logger;
        $this->routeKeeper = $routeKeeper;
    }

    /**
     * build the prod router
     * @param string $classCode
     * @return string The prod router
     */
    public function build(): string
    {
        return "adebipe/Router/RouterBuilder.php";
    }

    private function executeRoute(ReflectionMethod $method, array $parameters, Injector $injector): mixed
    {
        // Execute before Annotations
        foreach ($method->getAttributes() as $attribute) {
            if (is_subclass_of($attribute->getName(), BeforeRoute::class)) {
                $beforeRoute = $attribute->newInstance();
                $execute = new ReflectionMethod($beforeRoute, 'execute');
                $response = $injector->execute($execute, $beforeRoute, $parameters);
                if ($response instanceof Response) {
                    return $response;
                }
                if ($response === false) {
                    return new Response('An error occured', 500);
                }
                if ($response !== true) {
                    throw new \Exception('BeforeRoute ' . $attribute->getName() . ' returned an invalid value');
                }
            }
        }
        $response = $injector->execute($method, null, $parameters);
        // Check if the response is an instance of Response
        if (!($response instanceof Response)) {
            throw new \Exception('Response is not an instance of Response');
        }
        return $response;

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
        $this->routeKeeper->updateRoutes();
        $this->logger->info('Get response for request: ' . $request->uri);
        $add_to_injector = [
            Request::class => $request,
        ];
        // Find the route
        $result = $this->routeKeeper->findRoute($request->uri, $request->method);
        // Check if the route is not found
        if ($result === null) {
            throw new \Exception('An error occured while finding the route');
        }
        // Check if the route is not found
        if (is_numeric($result[0])) {
            return new Response($result[1], $result[0]);
        }
        $route = $result[0];
        $add_to_injector = array_merge($add_to_injector, $result[1]);
        return $this->executeRoute($route, $add_to_injector, $injector);
    }
}
