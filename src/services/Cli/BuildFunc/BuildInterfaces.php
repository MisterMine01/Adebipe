<?php


namespace Api\Cli;

use ReflectionClass;

function build_interfaces(string $servicesPath): array
    {
        $to_include = [];
        foreach (get_declared_interfaces() as $interface) {
            $interface = new ReflectionClass($interface);
            if (!preg_match('/^Api\\\\/', $interface->getName())) {
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