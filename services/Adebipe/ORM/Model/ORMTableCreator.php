<?php


namespace Adebipe\Model;

/**
 * Create the schema of the database
 * @package Adebipe\Model
 */
abstract class ORMTableCreator
{
    /**
     * The schema of the database
     * @var array
     */
    public array $database_schema = [];

    /**
     * The fixtures of the database
     */
    public array $fixtures = [];

    /**
     * Create a model
     * @param string $object_class
     * @return void
     */
    public function create_model(string $object_class)
    {
        $this->database_schema[$object_class] = $object_class;
    }

    /**
     * Get the schema of the database
     * @return array
     */
    public function getSchema(): array
    {
        return $this->database_schema;
    }

    /**
     * Get the fixtures of the database
     * @return array
     */
    public abstract function getFixtures(): array;
}
