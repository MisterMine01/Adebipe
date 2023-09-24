<?php

namespace Adebipe\Services\Generated;

use Adebipe\Model\Model;
use Adebipe\Model\Repository;
use Adebipe\Services\Interfaces\RegisterServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;
use Adebipe\Services\MsQl;

/**
 * The ORM service
 * This service is used to manage the models, the repositories and the database
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ORM implements RegisterServiceInterface, StarterServiceInterface
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
        // MODEL INIT GOES HERE
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
}
