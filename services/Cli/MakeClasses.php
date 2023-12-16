<?php

namespace Adebipe\Cli;

use Adebipe\Services\ConfigRunner;
use Adebipe\Services\Container;
use Adebipe\Services\Injector;
use Adebipe\Services\Interfaces\CreatorInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;
use Adebipe\Services\Logger;
use ReflectionClass;

/**
 * Make the classes from the Application namespace
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class MakeClasses
{
    public static Injector $injector;
    public static Container $container;

    /**
     * Make the classes from the Application namespace
     *
     * @param array<string> $classes       The list of the classes
     * @param ConfigRunner  $config_runner The config runner
     *
     * @return array<ReflectionClass>
     */
    public static function makeClasses(array $classes, ConfigRunner $config_runner = null): array
    {
        if ($config_runner === null) {
            $config_runner = new ConfigRunner();
        }
        $logger = new Logger();
        $logger->info('Initialize the services');
        $injector = new Injector($logger);
        MakeClasses::$injector = $injector;
        $injector->addService($logger);

        $container = $injector->createClass(new ReflectionClass(Container::class));
        if (!$container instanceof Container) {
            throw new \Exception('The container is not a container');
        }
        MakeClasses::$container = $container;
        $injector->addService($container);
        $injector->addService($injector);

        $container->addService($config_runner);
        $container->addService($logger);
        $container->addService($injector);
        $container->addService($container);

        $all_class = array();
        $atStart = array();
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                continue;
            }
            $reflection = new ReflectionClass($class);
            $all_class[] = $reflection;
            if (strpos($class, 'Adebipe\\Services\\') !== 0) {
                continue;
            }
            $container->addReflection($reflection);
            if (in_array($class, [ConfigRunner::class, Logger::class, Injector::class, Container::class])) {
                $logger->info('Skip service: ' . $class);
                continue;
            }
            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                continue;
            }
            if ($reflection->implementsInterface(CreatorInterface::class)) {
                $class = $injector->createClass($reflection);
                if ($class instanceof RegisterServiceInterface) {
                    $injector->addService($class);
                }
                if ($class instanceof StarterServiceInterface) {
                    $atStart_function = $reflection->getMethod('atStart');
                    $atStart[] = [$atStart_function, $class];
                }
                $container->addService($class);
            }
        }
        foreach ($atStart as $function) {
            $injector->execute($function[0], $function[1]);
        }
        $logger->atStart();
        return $all_class;
    }

    /**
     * Stop all the services
     *
     * @return void
     */
    public static function stopServices(): void
    {
        $logger = MakeClasses::$injector->getService(Logger::class);
        if (!$logger instanceof Logger) {
            return;
        }
        $logger->info('Stopping the services');
        foreach (MakeClasses::$container->getSubclassInterfaces(StarterServiceInterface::class) as $service) {
            if ($service::class === Logger::class) {
                continue;
            }
            $reflection = new ReflectionClass($service);
            $atEnd = $reflection->getMethod('atEnd');
            MakeClasses::$injector->execute($atEnd, $service);
        }
        $logger->atEnd();
    }
}
