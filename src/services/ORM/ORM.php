<?php

namespace Api\Services;

use Api\Model\Repository;
use Api\Services\Interfaces\RegisterServiceInterface;
use Api\Services\Interfaces\StarterServiceInterface;

class ORM implements RegisterServiceInterface, StarterServiceInterface
{
    private MsQl $msql;

    private $repository = array();

    public function atStart(MsQl $msql = null): void
    {
        $this->msql = $msql;
        $class_creator = getenv("ORM_TABLE_MODELS");
        $class_init = new $class_creator();
        $all_schema = $class_init->getSchema();
        foreach ($all_schema as $table_name => $object_class) {
            $this->repository[$table_name] = new Repository($object_class, $this->msql);
        }
    }


    public function atEnd(): void
    {
    }
}
