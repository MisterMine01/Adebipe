<?php

namespace Adebipe\Services\Generated;

use Adebipe\Model\Model;
use Adebipe\Model\Repository;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;
use Adebipe\Services\MsQl;

class ORM implements RegisterServiceInterface, StarterServiceInterface
{
    private MsQl $msql;

    private $repository = array();

    public static function classToTableName(string $class_name): string
    {
        return strtolower(substr($class_name, strrpos($class_name, '\\') + 1));
    }

    public function atStart(MsQl $msql = null): void
    {
        $this->msql = $msql;
        // MODEL INIT GOES HERE
        Model::$msql = $this->msql;
    }


    public function atEnd(): void
    {
    }

    public function getRepositories(): array
    {
        return $this->repository;
    }

    public function getRepository(string $object_class): Repository
    {
        return $this->repository[$object_class];
    }
}
