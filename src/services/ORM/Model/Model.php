<?php

namespace Adebipe\Model;

use Adebipe\Model\Type\SqlBasedTypeInterface;
use Adebipe\Services\MsQl;

abstract class Model {

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
            $this->properties[$key] = $value;
        }
    }
    public function getSchema(): array {
        return $this->schema;
    }

    public function getKey(): array {
        return array_keys($this->schema);
    }

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