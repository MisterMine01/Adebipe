<?php

namespace Adebipe\Builder;

use Adebipe\Cli\MakeClasses;
use Adebipe\Router\Annotations\AfterRoute;
use Adebipe\Router\Annotations\BeforeRoute;
use Adebipe\Services\Logger;
use Adebipe\Services\RouteKeeper;
use ReflectionClass;

/**
 * Builder of the RouteKeeper
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class RouteKeeperBuilder implements BuilderInterface
{
    private string $_salt;

    private array $_routes;

    /**
     * Return the files to include in the build
     *
     * @return array<string> The files to include in the build
     */
    public function includeFiles(): array
    {
        $classes = get_declared_classes();
        $classes = array_filter(
            $classes,
            function ($class) {
                return is_subclass_of($class, AfterRoute::class) || is_subclass_of($class, BeforeRoute::class);
            }
        );
        $classes = array_unique($classes);
        $files = array();
        foreach ($classes as $class) {
            $files[] = (new ReflectionClass($class))->getFileName();
        }
        return $files;
    }

    /**
     * Build the classes and write them in the temporary file
     *
     * @param string               $tmp_file The temporary file to write the classes
     * @param CoreBuilderInterface $core     The core builder
     *
     * @return void
     */
    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {

        $length = 22;
        $this->_salt = substr(
            str_shuffle(
                str_repeat(
                    $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
                    (int)ceil($length / strlen($x))
                )
            ),
            1,
            $length
        );
        $core->includeFolder('src/Controller');
        $file_code = file_get_contents(__DIR__ . '/RouteKeeper.php');
        $route_keeper = $core->getService(RouteKeeper::class);
        $route_keeper->updateRoutes("prod");

        $this->_routes = $route_keeper->getRoutes();

        $file_code = str_replace("[\"ROUTES GO HERE\"];", $this->_getRoutesCode(), $file_code);
        $file_code = str_replace("// CODE OF ROUTES GOES HERE", $this->_getRoutesFuncCode(), $file_code);
        $file_code = str_replace("// CODE OF USES GOES HERE", $this->_getUsesCode(), $file_code);

        file_put_contents($tmp_file, $file_code);
    }

    /**
     * Get the code of the routes
     *
     * @return string
     */
    private function _getRoutesCode(): string
    {
        $routesCode = array();
        foreach ($this->_routes as $route => $routeData) {
            $routesCode[$route] = array();
            foreach ($routeData as $method => $function) {
                $routesCode[$route][$method] = [$this->_getFunctionName($route, $method), $function[1]];
            }
        }
        return var_export($routesCode, true) . ';';
    }

    /**
     * Get the code of the uses
     *
     * @return string
     */
    private function _getUsesCode(): string
    {
        $all_files = array();
        foreach ($this->_routes as $route => $routeData) {
            foreach ($routeData as $method => $data) {
                $function = $data[0];
                $all_files[] = $function->getFileName();
            }
        }
        $all_files = array_unique($all_files);
        $uses = array();
        foreach ($all_files as $file) {
            $file = file_get_contents($file);
            $all_lines = explode(PHP_EOL, $file);
            foreach ($all_lines as $line) {
                if (preg_match("/^use .+;$/", $line)) {
                    $uses[] = $line;
                }
            }
        }
        $uses = array_unique($uses);
        return implode(PHP_EOL, $uses);
    }

    /**
     * Get the code of the routes functions
     *
     * @return string
     */
    private function _getRoutesFuncCode(): string
    {
        $code_routes = '';
        foreach ($this->_routes as $route => $routeData) {
            foreach ($routeData as $method => $data) {
                $function = $data[0];
                $start = $function->getStartLine() - 1;
                $end = $function->getEndLine();
                $length = $end - $start;
                $code = file($function->getFileName());
                $code = implode("", array_slice($code, $start, $length));
                // Change name of the function
                $code = str_replace(
                    'function ' . $function->getName(),
                    'function ' . $this->_getFunctionName($route, $method),
                    $code
                );
                $attributes = [];
                foreach ($function->getAttributes() as $attribute) {
                    MakeClasses::$container->getService(Logger::class)->info($attribute->getName());
                    if (is_subclass_of($attribute->getName(), BeforeRoute::class)) {
                        $attributes[] = $attribute;
                    }
                    if (is_subclass_of($attribute->getName(), AfterRoute::class)) {
                        $attributes[] = $attribute;
                    }
                }
                $before_attribute_code = '';
                foreach ($attributes as $attribute) {
                    $before_attribute_code .= '#[' . $attribute->getName() . '(';
                    $before_attribute_code .= ($attribute->getArguments() === [] ?
                        '' :
                        var_export($attribute->getArguments(), true)
                    );
                    $before_attribute_code .= ')]' . PHP_EOL;
                }
                $code = $before_attribute_code . $code;

                $code_routes .= $code . PHP_EOL;
            }
        }
        return $code_routes;
    }

    /**
     * Get the name of the function
     *
     * @param string $route  The route
     * @param string $method The method of the route
     *
     * @return string
     */
    private function _getFunctionName(string $route, string $method): string
    {
        $alea = crypt($route, $this->_salt);
        $alea = str_replace('/', '_', $alea);
        $alea = str_replace('.', '_', $alea);
        $alea = str_replace('-', '_', $alea);
        return 'route_' . $alea . '_' . $method;
    }
}
