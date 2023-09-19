<?php

namespace Adebipe\Cli;

use Adebipe\Services\Interfaces\BuilderServiceInterface;


/**
 * Build the services from the Application namespace
 * @param string $servicesPath The path of the services
 * @param array<ReflectionClass> $all_services The list of the services
 */
function build_services(string $servicesPath, array $all_services): array
{
    $container = MakeClasses::$container;
    $to_include = [];
    foreach ($all_services as $service) {
        if (preg_match('/^App\\\\Components\\\\/', $service->getName())) {
            continue;
        }
        $file = $service->getFileName();
        $file_code = file_get_contents($file);
        if ($service->implementsInterface(BuilderServiceInterface::class)) {
            $service_class = $container->getService($service->getName());
            $added = $service_class->appendFiles();
            if ($added !== null) {
                $to_include = array_merge($to_include, $added);
            }
            $file_code = $service_class->build($file_code);
            if ($file_code === null) {
                continue;
            }
        }
        $file_path = $servicesPath . '/' . $service->getShortName();
        while (file_exists($file_path . ".php")) {
            $file_path = $file_path . "_";
        }
        file_put_contents($file_path . '.php', $file_code);
        $to_include[] = substr($file_path, strlen($servicesPath . '/')) . '.php';
    }

    return $to_include;
}