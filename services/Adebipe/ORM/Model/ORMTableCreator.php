<?php

namespace Adebipe\Model;

use Adebipe\Builder\NoBuildable;

/**
 * Create the schema of the database
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[NoBuildable]
abstract class ORMTableCreator
{
    /**
     * The schema of the database
     *
     * @var array
     */
    public array $database_schema = [];

    /**
     * The fixtures of the database
     */
    public array $fixtures = [];

    /**
     * Create a model
     *
     * @param string $object_class The class of the object
     *
     * @return void
     */
    public function createModel(string $object_class): void
    {
        $this->database_schema[$object_class] = $object_class;
    }

    /**
     * Get the schema of the database
     *
     * @return array
     */
    public function getSchema(): array
    {
        return $this->database_schema;
    }

    /**
     * Get the fixtures of the database
     *
     * @return array
     */
    abstract public function getFixtures(): array;
}
