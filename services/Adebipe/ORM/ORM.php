<?php

namespace Adebipe\Services;

use Adebipe\Model\Model;
use Adebipe\Model\Repository;
use Adebipe\Services\Interfaces\BuilderServiceInterface;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;

/**
 * The ORM service
 * This service is used to manage the models, the repositories and the database
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ORM implements RegisterServiceInterface, StarterServiceInterface, BuilderServiceInterface
{
    private MsQl $_msql;

    private $_repository = array();

    /**
     * Get the table name from a model class name
     *
     * @param string $class_name The class name of the model
     *
     * @return string
     */
    public static function classToTableName(string $class_name): string
    {
        return strtolower(substr($class_name, strrpos($class_name, '\\') + 1));
    }

    /**
     * Function to run at the start of the application
     *
     * @param MsQl $msql The MsQl service
     *
     * @return void
     */
    public function atStart(MsQl $msql = null): void
    {
        $this->_msql = $msql;
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
            $this->_repository[$table_name] = new Repository($object_class, $this->_msql);
        }
        Model::$msql = $this->_msql;
    }

    /**
     * Function to run at the end of the application
     *
     * @return void
     */
    public function atEnd(): void
    {
    }


    /**
     * Get the service builder name
     *
     * @return string path to the builder of the service
     */
    public function build(): string
    {
        return "adebipe/ORM/ORMBuilder.php";
    }

    /**
     * Get the repositories
     *
     * @return array<Repository>
     */
    public function getRepositories(): array
    {
        return $this->_repository;
    }

    /**
     * Get a repository
     *
     * @param string $object_class The class name of the model
     *
     * @return Repository
     */
    public function getRepository(string $object_class): Repository
    {
        return $this->_repository[$object_class];
    }

    /**
     * Update the database
     * (for the moment, only create the tables, can't drop or alter)
     *
     * @return void
     */
    public function update(): void
    {
        $already_existed = $this->_msql->getTable();
        $already_table_name = array();
        foreach ($already_existed as $table) {
            $already_table_name[] = $table['TABLE_NAME'];
        }
        $class_creator = getenv("ORM_TABLE_MODELS");
        $class_init = new $class_creator();
        $fixtures = $class_init->getFixtures();
        foreach ($this->_repository as $table_name => $repository) {
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
