<?php


namespace Adebipe\Cli;

use ReflectionClass;

/**
 * Build the interfaces from the Application namespace
 * 
 * @param string $servicesPath The path of the services
 * @return array<string> The list of the interfaces
 */
function build_interfaces(string $servicesPath): array
    {
        $to_include = [];
        foreach (get_declared_interfaces() as $interface) {
            $interface = new ReflectionClass($interface);
            if (!preg_match('/^Adebipe\\\\/', $interface->getName())) {
                continue;
            }
            $file = $interface->getFileName();
            $file_code = file_get_contents($file);
            $file_path = $servicesPath . '/interfaces/' . $interface->getShortName() . '.php';
            file_put_contents($file_path, $file_code);
            $to_include[] = substr($file_path, strlen($servicesPath . '/'));
        }
        return $to_include;
    }