<?php

namespace Adebipe\Services\Generated;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Logger;
use ReflectionMethod;

// CODE OF USES GOES HERE

/**
 * Services to keep all routes
 * and find a route
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
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
     *
     * @var array
     */
    private array $_routes = ["ROUTES GO HERE"];

    /**
     * RouteKeeper constructor.
     *
     * @param Logger $_logger Logger of the application
     */
    public function __construct(private Logger $_logger)
    {
    }

    // CODE OF ROUTES GOES HERE

    /**
     * Get all the id of the route and assign is value from the uri
     *
     * @param string $route       The route
     * @param string $regex_route The regex of the route
     * @param string $uri         The uri
     *
     * @return array<string, mixed>
     */
    public function performRegex(string $route, string $regex_route, string $uri): array
    {
        $this->_logger->info('Perform regex for route: ' . $route);

        $to_inject = [];

        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route, $matches);
        $id = $matches[0];
        $this->_logger->info('Get id: ' . json_encode($id));
        preg_match($regex_route, $uri, $matches);
        $this->_logger->info('Get matches: ' . json_encode($matches));

        for ($i = 0; $i < count($id); $i++) {
            $id_sub = substr($id[$i], 1, -1);
            $to_inject[$id_sub] = $matches[$i + 1];
        }
        $this->_logger->info('Get to inject: ' . json_encode($to_inject));
        return $to_inject;
    }

    /**
     * Find a route
     *
     * @param string $path   The path
     * @param string $method The method
     *
     * @return array|null [function, [regex_result]] or null or [string, string]
     */
    public function findRoute(string $path, string $method): ?array
    {
        if (isset($this->_routes[$path])) {
            if (isset($this->_routes[$path][$method])) {
                $reflection = new ReflectionMethod($this, $this->_routes[$path][$method][0]);
                return [$reflection, []];
            }
            return [405, "Method not allowed"];
        }
        $route = null;
        $regex = [];

        foreach ($this->_routes as $key => $value) {
            if (preg_match('/^\/\^.*\$\/$/', $key) === 0) {
                continue;
            }
            if (preg_match($key, $path)) {
                $this->_logger->info('Regex match');
                if (!isset($this->_routes[$key][$method])) {
                    $this->_logger->info('Method not allowed');
                    return [405, "Method not allowed"];
                }
                $regex = $this->performRegex($value[$method][1], $key, $path);
                $route = $key;
                foreach ($regex as $key => $value) {
                    $add_to_injector[$key] = $value;
                }
                break;
            }
        }

        if ($route === null) {
            $this->_logger->info('Route not found');
            return [404, "Not found"];
        }
        $reflection = new ReflectionMethod($this, $this->_routes[$route][$method][0]);
        return [$reflection, $regex];
    }
}
