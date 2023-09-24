<?php

namespace Adebipe\Cli\Builder;

use Adebipe\Builder\CoreBuilderInterface;
use Adebipe\Cli\Includer;
use Adebipe\Services\Container;

/**
 * Keep all function for help the builder classes
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class BuilderHelper implements CoreBuilderInterface
{
    /**
     * Constructor
     *
     * @param Includer  $_includer  The includer
     * @param Container $_container The container
     */
    public function __construct(
        private Includer $_includer,
        private Container $_container
    ) {
    }

    /**
     * Include a folder and return all classes declared
     *
     * @param string $folder The folder to include
     *
     * @return array<string> The classes declared in the folder
     */
    public function includeFolder(string $folder): array
    {
        return $this->_includer->includeAllFile($folder);
    }

    /**
     * Get a service from the container
     *
     * @param string $service The service to get
     *
     * @return mixed The service
     */
    public function getService(string $service): mixed
    {
        return $this->_container->getService($service);
    }

    /**
     * Get the function name of a service in the build
     *
     * @param string $service The service to get
     *
     * @return string The function name of the service
     */
    public function getServiceFunctionName(string $service): string
    {
        return ServicesBuilder::getName($service);
    }
}
