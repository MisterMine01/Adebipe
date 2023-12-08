<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\RegisterServiceInterface;

/**
 * Injector of the services
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Injector implements RegisterServiceInterface
{
    private array $_services = [];

    /**
     * Injector of the services
     *
     * @param Logger $_logger The logger to use
     */
    public function __construct(
        private Logger $_logger
    ) {
    }

    /**
     * Add a service to the injector
     *
     * @param RegisterServiceInterface $class The class of the service
     *
     * @return void
     */
    public function addService(RegisterServiceInterface $class): void
    {
        $this->_logger->debug('Add service: ' . $class::class);
        $this->_services[$class::class] = $class;
    }

    /**
     * Get a service from the injector
     *
     * @param string $name The name of the service
     *
     * @return ?RegisterServiceInterface The service
     */
    public function getService(string $name): ?RegisterServiceInterface
    {
        if (isset($this->_services[$name])) {
            return $this->_services[$name];
        }
        return null;
    }

    /**
     * Inject the params of a method
     *
     * @param \ReflectionMethod $method The method to inject
     * @param array             $params The params to inject
     *
     * @return array The params with the injected params
     */
    public function injectParams(\ReflectionMethod $method, array $params = []): array
    {
        $method_params = $method->getParameters();
        $find_params = [];

        foreach ($method_params as $param) {
            $param_name = $param->getName();
            $param_type = $param->getType();

            if ($param_type === null) {
                throw new \Exception(
                    'Param ' . $param_name . ' in method ' . $method->getName() .
                        ' in class ' . $method->getDeclaringClass()->getName() . ' has no type'
                );
            }
            $not_null = str_replace("?", "", $param_type->__toString());
            if (in_array($not_null, array_keys($this->_services))) {
                $find_params[] = $this->_services[$not_null];
                continue;
            }
            if (in_array($not_null, array_keys($params))) {
                $find_params[] = $params[$not_null];
                continue;
            }
            if (isset($params[$param_name])) {
                $find_params[] = $params[$param_name];
                continue;
            }
            if ($param->isDefaultValueAvailable()) {
                $find_params[] = $param->getDefaultValue();
                continue;
            }
            if ($param->allowsNull()) {
                $find_params[] = null;
                continue;
            }
            throw new \Exception(
                'Param ' . $param_name . ' in method ' . $method->getName() .
                    ' in class ' . $method->getDeclaringClass()->getName() . ' can\'t be injected'
            );
        }
        return $find_params;
    }

    /**
     * Execute a function with the injected services
     *
     * @param \ReflectionMethod $method The method to execute
     * @param object|null       $class  The class of the method
     * @param array             $params The params who can be injected
     *
     * @return mixed The result of the function
     */
    public function execute(\ReflectionMethod $method, ?object $class, array $params = []): mixed
    {
        $params = $this->injectParams($method, $params);

        return $method->invokeArgs($class, $params);
    }

    /**
     * Create a class with the injected services
     *
     * @param \ReflectionClass $class  The class to create
     * @param array            $params The params who can be injected
     *
     * @return object The created class
     */
    public function createClass(\ReflectionClass $class, array $params = []): object
    {
        $constructor = $class->getConstructor();
        if ($constructor === null) {
            $this->_logger->debug('Class ' . $class->getName() . ' has no constructor');
            return $class->newInstance();
        }
        if (!$constructor->isPublic()) {
            throw new \Exception('Constructor of class ' . $class->getName() . ' is not public');
        }
        $params = $this->injectParams($constructor, $params);

        return $class->newInstanceArgs($params);
    }
}
