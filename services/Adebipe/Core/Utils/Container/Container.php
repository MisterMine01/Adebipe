<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\BuilderServicesInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;
use ReflectionClass;

/**
 * Contains all classes of the services
 * @package Adebipe\Services
 */
class Container implements RegisterServiceInterface
{
    /**
     * all services of the application
     * @var array<object> $services
     */
    private array $services = [];

    /**
     * ReflectionClass of all class
     * @var array<ReflectionClass> $reflections
     */
    private array $reflections = [];

    /**
     * Add a service to the container
     * @param string $name
     * @param object $service
     */
    public function addService(object $service): void
    {
        $this->services[$service::class] = $service;
    }

    /**
     * Get a service from the container
     * @param string $name
     * @return object
     */
    public function getService(string $name): object
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $this->createClass($this->getReflection($name));
        }
        return $this->services[$name];
    }

    private function createClass(ReflectionClass $reflection): object
    {
        $injector = $this->getService(Injector::class);
        return $injector->create_class($reflection);
    }

    /**
     * Get all services from the container
     * @return array<object>
     */
    public function getServices(): array
    {
        return $this->services;
    }

    /**
     * Add a ReflectionClass to the containera
     * @param ReflectionClass $reflection
     */
    public function addReflection(ReflectionClass $reflection): void
    {
        $this->reflections[$reflection->getName()] = $reflection;
    }

    /**
     * Get a ReflectionClass from the container
     * @param string $name
     * @return ReflectionClass
     */
    public function getReflection(string $name): ReflectionClass
    {
        return $this->reflections[$name];
    }

    /**
     * Get all ReflectionClass from the container
     * @return array<ReflectionClass>
     */
    public function getReflections(): array
    {
        return $this->reflections;
    }

    /**
     * Get all services who implements an interface
     * @param string $interface
     * @return array<object>
     */
    public function getSubclassInterfaces(string $subclass): array
    {
        $interfaces = [];
        foreach ($this->services as $name => $service) {
            if (is_subclass_of($service, $subclass)) {
                $interfaces[$name] = $service;
            }
        }
        return $interfaces;
    }
}
