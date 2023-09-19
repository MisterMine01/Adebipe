<?php

use Adebipe\Services\Logger;

class RouterBuilder implements BuilderInterface
{

    public function includeFiles(): array
    {
        return [];
    }

    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {

        $file_code = file_get_contents(__DIR__ . '/Router.php');
        $file_code = str_replace("[\"MIME TYPES GO HERE\"];", $this->getMimeCode(), $file_code);
    }

    public function getBuilderRouter(): string
    {
        $file_code = str_replace("[\"ROUTES GO HERE\"];", $this->getRoutesCode(), $file_code);
        $file_code = str_replace("// CODE OF ROUTES GOES HERE", $this->getRoutesFuncCode(), $file_code);
        $file_code = str_replace("// CODE OF USES GOES HERE", $this->getUsesCode(), $file_code);
        return $file_code;
    }

    private function getUsesCode(): string
    {
        $all_files = array();
        foreach ($this->routes as $route => $routeData) {
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

    private function getMimeCode(): string
    {
        $mime = json_decode(file_get_contents(__DIR__ . '/mime.json'), true);
        return var_export($mime, true) . ';';
    }

    private function getRoutesCode(): string
    {
        $routesCode = array();
        foreach ($this->routes as $route => $routeData) {
            $routesCode[$route] = array();
            foreach ($routeData as $method => $function) {
                $routesCode[$route][$method] = [$this->getFunctionName($route, $method), $function[1]];
            }
        }
        return var_export($routesCode, true) . ';';
    }

    private function getFunctionName(string $route, string $method): string
    {
        $alea = crypt($route, $this->salt);
        $alea = str_replace('/', '_', $alea);
        $alea = str_replace('.', '_', $alea);
        $alea = str_replace('-', '_', $alea);
        return 'route_' . $alea . '_' . $method;
    }

    private function getRoutesFuncCode(): string
    {
        $code_routes = '';
        foreach ($this->routes as $route => $routeData) {
            foreach ($routeData as $method => $data) {
                $function = $data[0];
                $start = $function->getStartLine() - 1;
                $end = $function->getEndLine();
                $length = $end - $start;
                $code = file($function->getFileName());
                $code = implode("", array_slice($code, $start, $length));
                // Change name of the function
                $code = str_replace('function ' . $function->getName(), 'function ' . $this->getFunctionName($route, $method), $code);
                $code_routes .= $code . PHP_EOL;
            }
        }
        return $code_routes;
    }
}
