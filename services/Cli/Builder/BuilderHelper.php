<?php

use Adebipe\Cli\Includer;
use Adebipe\Services\Container;

class BuilderHelper implements CoreBuilderInterface
{
    public function __construct(
        private Includer $_includer,
        private Container $_container
    ) {
    }

    public function includeFolder(string $folder): array
    {
        return $this->_includer->includeAllFile($folder);
    }

    public function getService(string $service): mixed
    {
        return $this->_container->getService($service);
    }

    public function getServiceFunctionName(string $service): string
    {
        return ServicesBuilder::getName($service);
    }
}
