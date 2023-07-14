<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\BuilderServicesInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;

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
        return $this->services[$name];
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
     * Get all services that implements BuilderServicesInterface
     * @return array<object>
     */
    public function getBuildServices(): array
    {
        $services = [];

        foreach ($this->services as $name => $service) {
            if (is_subclass_of($service, BuilderServicesInterface::class)) {
                $services[$name] = $service;
            }
        }

        return $services;
    }

    /**
     * Get all services that implements StarterServiceInterface
     * @return array<object>
     */
    public function getStarterServices(): array
    {
        $services = [];

        foreach ($this->services as $name => $service) {
            if (is_subclass_of($service, StarterServiceInterface::class)) {
                $services[$name] = $service;
            }
        }
        return array_reverse($services);
    }

    /**
     * Get all services that implements RegisterServiceInterface
     * @return array<object>
     */
    public function getRegisterServices(): array
    {
        $services = [];

        foreach ($this->services as $name => $service) {
            if (is_subclass_of($service, RegisterServiceInterface::class)) {
                $services[$name] = $service;
            }
        }

        return $services;
    }
}
