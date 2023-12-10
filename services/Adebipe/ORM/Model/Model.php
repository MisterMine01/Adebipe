<?php

namespace Adebipe\Model;

use Adebipe\Model\Type\ModelTypeInterface;
use Adebipe\Model\Type\SqlBasedTypeInterface;
use Adebipe\Services\MsQl;

/**
 * Abstract class for models
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
abstract class Model implements ModelInterface
{
    public static MsQl $msql;
    public static string $repository = Repository::class;

    private array $_properties = [];
    private array $_schema = [];

    /**
     * Create the schema of the model
     *
     * @return array<string, ModelTypeInterface>
     */
    abstract public static function createSchema(): array;

    /**
     * Create a model
     *
     * @param array $data The data of the model
     */
    public function __construct(array $data)
    {
        $this->_schema = static::createSchema();
        $all_key = array_keys($this->_schema);
        foreach ($data as $key => $value) {
            if (!in_array($key, $all_key)) {
                throw new \Exception("Unknown key $key");
            }
            if (is_subclass_of($this->_schema[$key], SqlBasedTypeInterface::class)) {
                continue;
            }
            $this->_properties[$key] = $value;
        }
    }

    /**
     * Get the schema of the model
     *
     * @return array
     */
    public function getSchema(): array
    {
        return $this->_schema;
    }

    /**
     * Get the key of the model (the columns)
     *
     * @return array
     */
    public function getKey(): array
    {
        return array_keys($this->_schema);
    }

    /**
     * Get the values of the model
     *
     * @return array
     */
    public function getValues(): array
    {
        $schema = $this->getSchema();
        $values = [];
        foreach ($schema as $key => $value) {
            if (is_subclass_of($value, SqlBasedTypeInterface::class)) {
                continue;
            }
            $values[$key] = $this->_properties[$key];
        }
        return $values;
    }

    /**
     * Add a value to a complex type (like a relation)
     *
     * @param string $name  The name of the column
     * @param object $value The value to add
     *
     * @return bool
     */
    public function addTo(string $name, object $value): bool
    {
        $schema = $this->getSchema();
        if (!isset($schema[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $schema[$name];
        if (!$schema instanceof SqlBasedTypeInterface) {
            throw new \Exception("You can't add value to " . $name);
        }
        return $schema->addToDb(Model::$msql, $this->id, $value);
    }

    /**
     * Delete a value to a complex type (like a relation)
     *
     * @param string $name  The name of the column
     * @param object $value The value to delete
     *
     * @return bool
     */
    public function deleteTo(string $name, object $value): bool
    {
        $schema = $this->getSchema();
        if (!isset($schema[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $schema[$name];
        if (!$schema instanceof SqlBasedTypeInterface) {
            throw new \Exception("You can't delete value to $name");
        }
        return $schema->deleteToDb(Model::$msql, $this->id, $value);
    }


    /**
     * Get the value of a property
     *
     * @param string $name The name of the property
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $schema = $this->getSchema();
        if (!isset($schema[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $schema[$name];
        if (!isset($this->_properties[$name])) {
            if (!$schema instanceof SqlBasedTypeInterface) {
                return null;
            }
            $this->_properties[$name] = $schema->getResultFromDb(Model::$msql, $this->id);
        }
        $schema = $this->getSchema()[$name];
        return $this->_properties[$name];
    }

    /**
     * Set the value of a property
     *
     * @param string $name  The name of the property
     * @param mixed  $value The value of the property
     *
     * @throws \Exception
     *
     * @return void
     */
    public function __set(string $name, $value): void
    {
        if (!isset($this->_properties[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $this->getSchema()[$name];
        if (is_subclass_of($schema, SqlBasedTypeInterface::class)) {
            throw new \Exception("You can't set value to $name");
        }
        $this->_properties[$name] = $value;
    }

    /**
     * Check if a property is set
     *
     * @param string $name The name of the property
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->{$name});
    }
}
