<?php

namespace App\Components;

use Adebipe\Components\Interfaces\ComponentInterface;
use Adebipe\Router\Annotations\Route;
use Adebipe\Router\JsonResponse;
use Adebipe\Router\Response;
use Adebipe\Services\Container;
use Adebipe\Services\Logger;
use Adebipe\Services\Renderer;

class ServicesComponents implements ComponentInterface
{
    #[Route(path: '/adebipe/services', method: 'GET')]
    public static function index(Container $container, Renderer $renderer): Response
    {
        $services = $container->getReflections();
        $decoded_services = [];
        foreach ($services as $reflection) {
            $decoded_service = [
                'name' => $reflection->getShortName(),
                'namespace' => $reflection->getNamespaceName(),
                'long_name' => $reflection->getName(),
                'methods' => [],
            ];
            foreach ($reflection->getMethods() as $method) {
                if ($method->isPrivate() || $method->isProtected()) {
                    continue;
                }
                $comment = $method->getDocComment();
                $decoded_comment = explode("\n", $comment);
                $decoded_comment = array_map(fn($line) => trim($line, " \t\n\r\0\x0B*"), $decoded_comment);
                $decoded_comment = array_filter($decoded_comment, fn($line) => $line !== '');
                $decoded_comment = array_values($decoded_comment);
                $parameter_comment = [];
                foreach ($decoded_comment as $key => $line) {
                    if (strpos($line, '@') === 0) {
                        unset($decoded_comment[$key]);
                    }
                    if (strpos($line, '@') === 0) {
                        unset($decoded_comment[$key]);
                    }
                }
                $decoded_comment = implode(' ', $decoded_comment);
                $decoded_comment = substr($decoded_comment, 1, -2);
                $decoded_method = [
                    'name' => $method->getName(),
                    'comment' => $decoded_comment,
                    'parameters' => [],
                ];
                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type === null) {
                        $comment = $parameter_comment[$parameter->getName()] ?? null;
                        $decoded_parameter = [
                            'name' => $parameter->getName(),
                            'comment' => $comment,
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
        return $renderer->render('Adebipe/services/services.php', $environment);
    }

}