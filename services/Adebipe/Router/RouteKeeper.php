<?php

namespace Adebipe\Services;

use Adebipe\Router\Annotations\RegexSimple;
use Adebipe\Router\Annotations\Route;
use Adebipe\Services\Interfaces\BuilderServiceInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use ReflectionMethod;

/**
 * Services to keep all routes
 * and find a route
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class RouteKeeper implements RegisterServiceInterface, BuilderServiceInterface
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
     * @var array<string, array<string, array<callable, string>>>
     */
    private array $_routes = [];

    /**
     * RouteKeeper constructor.
     *
     * @param Logger $_logger Logger of the application
     */
    public function __construct(private Logger $_logger)
    {
    }

    /**
     * Get the service builder name
     *
     * @return string path to the builder of the service
     */
    public function build(): string
    {
        return "adebipe/Router/RouteKeeperBuilder.php";
    }

    /**
     * Add a route
     *
     * @param string           $path     Path of the route
     * @param string           $method   Method of the route
     * @param ReflectionMethod $function Function of the route
     * @param string           $route    Route of the route
     *
     * @return void
     */
    public function addRoute(string $path, string $method, ReflectionMethod $function, string $route): void
    {
        $this->_routes[$route][$method] = [$function, $path];
    }

    /**
     * Delete a route
     *
     * @param string $path   Path of the route
     * @param string $method Method of the route
     *
     * @return void
     */
    public function deleteRoute(string $path, string $method): void
    {
        unset($this->_routes[$path][$method]);
    }

    /**
     * Delete all routes
     *
     * @return void
     */
    public function deleteAllRoutes(): void
    {
        $this->_routes = [];
    }

    /**
     * Check if a route already exist
     *
     * @param string $path   Path of the route
     * @param string $method Method of the route
     *
     * @return bool
     */
    public function routeAlreadyExist(string $path, string $method): bool
    {
        return isset($this->_routes[$path]) && isset($this->_routes[$path][$method]);
    }

    /**
     * Get all routes
     *
     * @return array<string, array<string, array<callable, string>>>
     */
    public function getRoutes(): array
    {
        return $this->_routes;
    }

    /**
     * Update the routes
     *
     * @param string $env_wanted The env wanted (???, dev, prod)
     *
     * @return void
     */
    public function updateRoutes(string $env_wanted = "???"): void
    {
        $this->deleteAllRoutes();
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
                if ($this->routeAlreadyExist($route->path, $route->method)) {
                    throw new \Exception(
                        'Route ' . $route->path . ' with method ' .
                            $route->method . ' already exists'
                    );
                }
                if ($env_wanted !== "???" && $route->env !== $env_wanted) {
                    continue;
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
                $this->addRoute($route->path, $route->method, $method, $regex_decoded);
            }
        }
    }

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
                return [$this->_routes[$path][$method][0], []];
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
        return [$this->_routes[$route][$method][0], $regex];
    }
}
