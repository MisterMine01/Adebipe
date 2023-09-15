<?php

namespace App\Components;

use Adebipe\Components\Interfaces\ComponentInterface;
use Adebipe\Router\Annotations\Route;
use Adebipe\Router\JsonResponse;
use Adebipe\Router\Response;
use Adebipe\Services\Container;
use Adebipe\Services\Renderer;

class ServicesComponents implements ComponentInterface
{
    #[Route(path: '/adebipe/services', method: 'GET')]
    public static function index(Container $container, Renderer $renderer): Response
    {
        $services = $container->getServices();
        $decoded_services = [];
        foreach ($services as $service) {
            $reflection = new \ReflectionClass($service);
            $decoded_service = [
                'name' => $reflection->getShortName(),
                'methods' => [],
            ];
            foreach ($reflection->getMethods() as $method) {
                $decoded_method = [
                    'name' => $method->getName(),
                    'comment' => $method->getDocComment(),
                    'parameters' => [],
                ];
                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type === null) {
                        $decoded_parameter = [
                            'name' => $parameter->getName(),
                            'type' => 'mixed'
                        ];
                        $decoded_method['parameters'][] = $decoded_parameter;
                        continue;
                    }
                    $decoded_parameter = [
                        'name' => $parameter->getName(),
                        'type' => $type->getName(),
                    ];
                    $decoded_method['parameters'][] = $decoded_parameter;
                }
                $decoded_service['methods'][] = $decoded_method;
            }
            $decoded_services[] = $decoded_service;
        }
        
        // $renderer->addGlobal('services', $decoded_services);
        $environment = [
            'services' => $decoded_services,
        ];
        return $renderer->render('development/adebipe/services/services.php', $environment);
    }

}