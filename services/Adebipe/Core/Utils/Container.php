<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\RegisterServiceInterface;
use ReflectionClass;

/**
 * Contains all classes of the services
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Container implements RegisterServiceInterface
{
    private array $_services = [];

    private array $_reflections_classes = [];

    /**
     * Add a service to the container
     *
     * @param object $service The service to add
     *
     * @return void
     */
    public function addService(object $service): void
    {
        $this->_services[$service::class] = $service;
    }

    /**
     * Get a service from the container
     *
     * @param string $name The name of the service
     *
     * @return object
     */
    public function getService(string $name): object
    {
        if (!isset($this->_services[$name])) {
            $this->_services[$name] = $this->_createClass($this->getReflection($name));
        }
        return $this->_services[$name];
    }

    /**
     * Create a class with the injector
     *
     * @param ReflectionClass $reflection The reflection of the class
     *
     * @return object The created class
     */
    private function _createClass(ReflectionClass $reflection): object
    {
        $injector = $this->getService(Injector::class);
        return $injector->create_class($reflection);
    }

    /**
     * Get all services from the container
     *
     * @return array<object>
     */
    public function getServices(): array
    {
        return $this->_services;
    }

    /**
     * Add a ReflectionClass to the containera
     *
     * @param ReflectionClass $reflection The reflection to add
     *
     * @return void
     */
    public function addReflection(ReflectionClass $reflection): void
    {
        $this->_reflections_classes[$reflection->getName()] = $reflection;
    }

    /**
     * Get a ReflectionClass from the container
     *
     * @param string $name The name of the ReflectionClass
     *
     * @return ReflectionClass
     */
    public function getReflection(string $name): ReflectionClass
    {
        return $this->_reflections_classes[$name];
    }

    /**
     * Get all ReflectionClass from the container
     *
     * @return array<ReflectionClass>
     */
    public function getReflections(): array
    {
        return $this->_reflections_classes;
    }

    /**
     * Get all services who implements an interface
     *
     * @param string $subclass The interface to check
     *
     * @return array<object>
     */
    public function getSubclassInterfaces(string $subclass): array
    {
        $interfaces = [];
        foreach ($this->_services as $name => $service) {
            if (is_subclass_of($service, $subclass)) {
                $interfaces[$name] = $service;
            }
        }
        return $interfaces;
    }
}
