<?php

namespace Adebipe\Services\Generated;

use Adebipe\Router\Response;
use Adebipe\Services\Injector;
use Adebipe\Services\Interfaces\CreatorInterface;
use Adebipe\Services\Logger;
use Adebipe\Services\Settings;
use ReflectionMethod;

// CODE OF USES GOES HERE

/**
 * Services to manage routes
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Router implements CreatorInterface
{
    /**
     * Mime types
     *
     * @var array
     */
    private array $_mime = ["MIME TYPES GO HERE"];

    /**
     * Constructor
     *
     * @param Logger      $_logger      Logger of the application
     * @param RouteKeeper $_routeKeeper RouteKeeper of the application
     */
    public function __construct(
        private Logger $_logger,
        private RouteKeeper $_routeKeeper
    ) {
    }

    // CODE OF ROUTES GOES HERE

    /**
     * Get the response for a request
     *
     * @param Request  $request  Request to get the response
     * @param Injector $injector Injector of the application
     *
     * @return Response
     */
    public function getResponse(\Adebipe\Router\Request $request, Injector $injector): Response
    {
        $header = [];
        if ($request->origin) {
            $allowedOrigin = Settings::getConfig('APP.CORS');
            if ($allowedOrigin === "*") {
                $allowedOrigin = [$request->origin];
            }
            if (in_array($request->origin, $allowedOrigin)) {
                $header['Access-Control-Allow-Origin'] = $request->origin;
            } else {
                return new \Adebipe\Router\Response('Not allowed', 403);
            }
        }
        // Remove double slashes or more in the uri
        $request->uri = preg_replace('(\/+)', '/', $request->uri);

        if (is_file("public" . $request->uri)) {
            // The request is a file
            $this->_logger->info('Get file: ' . $request->uri);
            return new \Adebipe\Router\Response(
                file_get_contents("public" . $request->uri),
                200,
                [
                'Content-Type' => $this->_mime[pathinfo("public" . $request->uri, PATHINFO_EXTENSION)]
                ]
            );
        }
        // update the routes
        $this->_logger->info('Get response for request: ' . $request->uri);
        $add_to_injector = [
            Request::class => $request,
        ];
        // Find the route
        $result = $this->_routeKeeper->findRoute($request->uri, $request->method);
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
        return $this->_executeRoute($route, $add_to_injector, $injector);
    }

    /**
     * Execute a route with the parameters
     *
     * @param ReflectionMethod $method     Method to execute
     * @param array            $parameters Parameters to inject
     * @param Injector         $injector   Injector of the application
     *
     * @return Response
     */
    private function _executeRoute(ReflectionMethod $method, array $parameters, Injector $injector): mixed
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
}
