<?php

namespace Adebipe\Model;

use Adebipe\Model\Type\SqlBasedTypeInterface;
use Adebipe\Services\MsQl;

abstract class Model implements ModelInterface
{

    public static MsQl $msql;
    private array $properties = [];
    private array $schema = [];

    abstract public static function createSchema(): array;

    public function __construct(array $data)
    {
        $this->schema = static::createSchema();
        $all_key = array_keys($this->schema);
        foreach ($data as $key => $value) {
            if (!in_array($key, $all_key)) {
                throw new \Exception("Unknown key $key");
            }
            if (is_subclass_of($this->schema[$key], SqlBasedTypeInterface::class)) {
                continue;
            }
            $this->properties[$key] = $value;
        }
    }
    public function getSchema(): array
    {
        return $this->schema;
    }

    public function getKey(): array
    {
        return array_keys($this->schema);
    }

    public function getValues(): array
    {
        $schema = $this->getSchema();
        $values = [];
        foreach ($schema as $key => $value) {
            if (is_subclass_of($value, SqlBasedTypeInterface::class)) {
                continue;
            }
            $values[$key] = $this->properties[$key];
        }
        return $values;
    }

    public function addTo(string $name, object $value): bool
    {
        $schema = $this->getSchema();
        if (!isset($schema[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $schema[$name];
        if (!is_subclass_of($schema, SqlBasedTypeInterface::class)) {
            throw new \Exception("You can't add value to $name");
        }
        return $schema->addToDb(Model::$msql, $this->id, $value);
    }

    public function deleteTo(string $name, object $value): bool
    {
        $schema = $this->getSchema();
        if (!isset($schema[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $schema[$name];
        if (!is_subclass_of($schema, SqlBasedTypeInterface::class)) {
            throw new \Exception("You can't delete value to $name");
        }
        return $schema->deleteToDb(Model::$msql, $this->id, $value);
    }


    /**
     * Get the value of a property
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        $schema = $this->getSchema();
        if (!isset($schema[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $schema[$name];
        if (!isset($this->properties[$name])) {
            if (!is_subclass_of($schema, SqlBasedTypeInterface::class)) {
                return null;
            }
            $this->properties[$name] = $schema->getResultFromDb(Model::$msql, $this->id);
        }
        $schema = $this->getSchema()[$name];
        return $this->properties[$name];
    }

    /**
     * Set the value of a property
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        if (!isset($this->properties[$name])) {
            throw new \Exception("Unknown key $name");
        }
        $schema = $this->getSchema()[$name];
        if (is_subclass_of($schema, SqlBasedTypeInterface::class)) {
            throw new \Exception("You can't set value to $name");
        }
        $this->properties[$name] = $value;
    }

    public function __isset(string $name)
    {
        return isset($this->{$name});
    }
}