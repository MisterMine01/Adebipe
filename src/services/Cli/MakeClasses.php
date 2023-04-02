<?php

namespace Api\Cli;

use Api\Services\Container;
use Api\Services\Dotenv;
use Api\Services\Injector;
use Api\Services\Interfaces\RegisterServiceInterface;
use Api\Services\Interfaces\StarterServiceInterface;
use Api\Services\Logger;
use ReflectionClass;

class MakeClasses {
    public static Injector $injector;
    public static Container $container;

    public static function makeClasses(array $classes): void
    {
        $dotenv = new Dotenv();
        $logger = new Logger();
        $logger->info('Initialize the services');
        $injector = new Injector($logger);
        MakeClasses::$injector = $injector;
        $injector->addService($logger);
        

        $container = $injector->create_class(new ReflectionClass(Container::class));
        MakeClasses::$container = $container;
        $injector->addService($container);
        $injector->addService($injector);

        $container->addService($dotenv);
        $container->addService($logger);
        $container->addService($injector);
        $container->addService($container);

        foreach ($classes as $class) {
            if (strpos($class, 'Api\\Services\\') !== 0)
            {
                continue;
            }
            if (in_array($class, [
                Dotenv::class,
                Logger::class,
                Injector::class,
                Container::class
            ])) {
                $logger->info('Skip service: ' . $class);
                continue;
            }
            $reflection = new ReflectionClass($class);
            $class = $injector->create_class($reflection);
            if ($reflection->implementsInterface(RegisterServiceInterface::class))
            {
                $injector->addService($class);
            }
            if ($reflection->implementsInterface(StarterServiceInterface::class))
            {
                $atStart = $reflection->getMethod('atStart');
                $injector->execute($atStart, $class);
            }
            $container->addService($class);
        }
    }

    public static function stopServices(): void
    {
        $logger = MakeClasses::$injector->getService(Logger::class);
        $logger->info('Stopping the services');
        foreach (MakeClasses::$container->getStarterServices() as $service) {
            $reflection = new ReflectionClass($service);
            $atEnd = $reflection->getMethod('atEnd');
            MakeClasses::$injector->execute($atEnd, $service);
        }
    }
}