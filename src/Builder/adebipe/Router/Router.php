<?php

namespace Adebipe\Services\Generated;

use Adebipe\Router\Response;
use Adebipe\Services\Injector;
use Adebipe\Services\Logger;
use ReflectionMethod;

// CODE OF USES GOES HERE

class Router
{
    /**
     * Mime types
     * @var array
     */
    private array $_mime = ["MIME TYPES GO HERE"];

    /**
     * Logger
     * @var Logger
     */
    private Logger $logger;

    private RouteKeeper $routeKeeper;

    /**
     * Constructor
     * @param Logger $logger
     */
    public function __construct(Logger $logger, RouteKeeper $routeKeeper)
    {
        $this->logger = $logger;
        $this->routeKeeper = $routeKeeper;
    }

    // CODE OF ROUTES GOES HERE


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
    public function getResponse(\Adebipe\Router\Request $request, Injector $injector): Response
    {
        // Remove double slashes or more in the uri
        $request->uri = preg_replace('(\/+)', '/', $request->uri);

        if (is_file("public" . $request->uri)) {
            // The request is a file
            $this->logger->info('Get file: ' . $request->uri);
            return new \Adebipe\Router\Response(file_get_contents("public" . $request->uri), 200, [
                'Content-Type' => $this->_mime[pathinfo("public" . $request->uri, PATHINFO_EXTENSION)]
            ]);
        }
        // update the routes
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
            return new \Adebipe\Router\Response($result[1], $result[0]);
        }
        $route = $result[0];
        $add_to_injector = array_merge($add_to_injector, $result[1]);
        return $this->executeRoute($route, $add_to_injector, $injector);
    }

    private function executeRoute(ReflectionMethod $method, array $parameters, Injector $injector): mixed
    {
        // Execute before Annotations
        /*foreach ($method->getAttributes() as $attribute) {
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
        }*/
        $response = $injector->execute($method, null, $parameters);
        // Check if the response is an instance of Response
        if (!($response instanceof Response)) {
            throw new \Exception('Response is not an instance of Response');
        }
        return $response;

    }
}
