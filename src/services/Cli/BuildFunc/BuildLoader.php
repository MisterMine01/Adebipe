<?php

use Api\Services\Container;
use Api\Services\Injector;
use Api\Services\Interfaces\RegisterServiceInterface;
use Api\Services\Interfaces\StarterServiceInterface;

/**
 * Get the parameters of the constructor and return the code to inject them
 * @param ReflectionMethod $func The constructor
 * @param array<string> $all_services_existed The list of the services already generated
 */
function getParameters($func, $all_services_existed): string
{
    $params = $func->getParameters();
    $params_code = '';
    foreach ($params as $param) {
        $param_class = $param->getClass();
        if ($param_class === null) {
            continue;
        }
        $param_class_name = $param_class->getName();
        if (in_array($param_class_name, array_keys($all_services_existed))) {
            $params_code .= $all_services_existed[$param_class_name] . ', ';
        }
    }
    return $params_code;
}

/**
 * Set the constructor of a service
 * @param ReflectionClass $service The service
 * @param array<string> $all_services_existed The list of the services already generated
 */
function setConstructor($service, $all_services_existed): string
{
    $constructor = $service->getConstructor();
    if ($constructor === null) {
        return "$" . $service->getShortName() . " = new " . $service->getName() . "();";
    }
    $params_code = getParameters($constructor, $all_services_existed);
    $params_code = substr($params_code, 0, -2);
    return "$" . $service->getShortName() . " = new " . $service->getName() . "($params_code);";
}

/**
 * Continue the loader with generating the code of the services
 * @param string $loaderPath The path of the loader
 * @param array<ReflectionClass> $all_services The list of the services
 */
function continue_loader(string $loaderPath, array $all_services): void
{
    $all_services_existed = array();
    foreach ($all_services as $service) {
        if (preg_match('/^Api\\\\Services\\\\/', $service->getName())) {
            $contructor = setConstructor($service, $all_services_existed);
            // Get the name of the variable
            $all_services_existed[$service->getName()] = substr($contructor, 0, strpos($contructor, ' = '));
            file_put_contents($loaderPath, $contructor . PHP_EOL, FILE_APPEND);
        }
    }
    $atEnds = [];
    foreach ($all_services_existed as $class => $var_name) {
        $reflection = new ReflectionClass($class);
        file_put_contents($loaderPath, $all_services_existed[Container::class] . "->addService($var_name);" . PHP_EOL, FILE_APPEND);
        if ($reflection->implementsInterface(RegisterServiceInterface::class)) {
            file_put_contents($loaderPath, $all_services_existed[Injector::class] . "->addService($var_name);" . PHP_EOL, FILE_APPEND);
        }
        if ($reflection->implementsInterface(StarterServiceInterface::class)) {
            $atStart = $reflection->getMethod('atStart');
            $params_code = getParameters($atStart, $all_services_existed);
            $params_code = substr($params_code, 0, -2);
            file_put_contents($loaderPath, $var_name . "->atStart($params_code);" . PHP_EOL, FILE_APPEND);

            $atEnd = $reflection->getMethod('atEnd');
            $params_code = getParameters($atEnd, $all_services_existed);
            $params_code = substr($params_code, 0, -2);
            $atEnds[] = [$var_name, $params_code];
        }
    }

    file_put_contents($loaderPath, "function atEnd() {" . PHP_EOL, FILE_APPEND);
    foreach ($atEnds as $atEnd) {
        file_put_contents($loaderPath, "\$GLOBALS['" . substr($atEnd[0], strpos($atEnd[0], '$') + 1) . "']->atEnd(" . $atEnd[1] . ");" . PHP_EOL, FILE_APPEND);
    }
    file_put_contents($loaderPath, "}" . PHP_EOL, FILE_APPEND);
}
