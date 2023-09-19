<?php

namespace Adebipe\Services\Generated;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Logger;
use ReflectionMethod;
// CODE OF USES GOES HERE

class RouteKeeper implements RegisterServiceInterface
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
    private array $routes = ["ROUTES GO HERE"];

    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
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
     * Find a route
     * @param string $path
     * @param string $method
     * @return array|null [function, [regex_result]] or null or [string, string]
     */
    public function findRoute(string $path, string $method): ?array
    {
        if (isset($this->routes[$path])) {
            if (isset($this->routes[$path][$method])) {
                return [$this->routes[$path][$method][0], []];
            }
            return [405, "Method not allowed"];
        }
        $route = null;
        $regex = [];

        foreach ($this->routes as $key => $value) {
            if (preg_match('/^\/\^.*\$\/$/', $key) === 0) {
                continue;
            }
            if (preg_match($key, $path)) {
                $this->logger->info('Regex match');
                if (!isset($this->routes[$key][$method])) {
                    $this->logger->info('Method not allowed');
                    return [405, "Method not allowed"];
                }
                $regex = $this->perform_regex($value[$method][1], $key, $path);
                $route = $key;
                foreach ($regex as $key => $value) {
                    $add_to_injector[$key] = $value;
                }
                break;
            }
        }

        if ($route === null) {
            $this->logger->info('Route not found');
            return [404, "Not found"];
        }
        return [$this->routes[$route][$method][0], $regex];
    }
}