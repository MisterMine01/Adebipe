<?php

namespace Adebipe\Services;

use Adebipe\Model\Model;
use Adebipe\Model\Repository;
use Adebipe\Services\Interfaces\BuilderServiceInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;

class ORM implements RegisterServiceInterface, StarterServiceInterface, BuilderServiceInterface
{
    private MsQl $msql;

    private $repository = array();

    public static function class_to_table_name(string $class_name): string
    {
        return strtolower(substr($class_name, strrpos($class_name, '\\') + 1));
    }

    public function atStart(MsQl $msql = null): void
    {
        $this->msql = $msql;
        $class_creator = getenv("ORM_TABLE_MODELS");
        if (!$class_creator) {
            throw new \Exception("ORM_TABLE_MODELS environment variable not set");
        }
        if (!class_exists($class_creator)) {
            if (getenv("ENV") == 'build') {
                return;
            }
        }
        $class_init = new $class_creator();
        $all_schema = $class_init->getSchema();
        foreach ($all_schema as $table_name => $object_class) {
            $this->repository[$table_name] = new Repository($object_class, $this->msql);
        }
        Model::$msql = $this->msql;
    }


    public function atEnd(): void
    {
    }

    public function build(): string
    {
        return "adebipe/ORM/ORMBuilder.php";
    }

    public function getRepositories(): array
    {
        return $this->repository;
    }

    public function getRepository(string $object_class): Repository
    {
        return $this->repository[$object_class];
    }

    public function update(): void
    {
        $already_existed = $this->msql->getTable();
        $already_table_name = array();
        foreach ($already_existed as $table) {
            $already_table_name[] = $table['TABLE_NAME'];
        }
        $class_creator = getenv("ORM_TABLE_MODELS");
        $class_init = new $class_creator();
        $fixtures = $class_init->getFixtures();
        foreach ($this->repository as $table_name => $repository) {
            if (!in_array($table_name, $already_table_name)) {
                $repository->createTable();
                if (array_key_exists($table_name, $fixtures)) {
                    foreach ($fixtures[$table_name] as $fixture) {
                        $object = $repository->getObjectClass($fixture);
                        $repository->save($object);
                    }
                }
            }
        }
    }
}
