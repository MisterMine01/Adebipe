<?php

namespace Adebipe\Builder;

/**
 * Core builder for the builder classes
 * Keep all function for help the builder classes
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[NoBuildable]
interface CoreBuilderInterface
{
    /**
     * Include a folder and return all classes declared
     *
     * @param string $folder The folder to include
     *
     * @return array<string> The classes declared in the folder
     */
    public function includeFolder(string $folder): array;

    /**
     * Get a service from the container
     *
     * @param string $service The service to get
     *
     * @return mixed The service
     */
    public function getService(string $service): mixed;

    /**
     * Get the function name of a service in the build
     *
     * @param string $service The service to get
     *
     * @return string The function name of the service
     */
    public function getServiceFunctionName(string $service): string;
}
