<?php

namespace Api\Cli;

use Api\Services\Interfaces\BuilderServiceInterface;



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
