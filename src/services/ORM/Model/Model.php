<?php

namespace Api\Model;

use Api\Model\Type\SqlBasedTypeInterface;
use Api\Services\MsQl;

abstract class Model {

    private MsQl $msql;
    private array $properties = [];

    public function __construct(MsQl $msql, array $data)
    {
        $this->msql = $msql;
        $all_key = array_keys(static::$schema);
        foreach ($data as $key => $value) {
            if (!in_array($key, $all_key)) {
                throw new \Exception("Unknown key $key");
            }
            $this->properties[$key] = $value;
        }
    }
    public function getSchema(): array {
        return static::$schema;
    }

    public function getKey(): array {
        return array_keys(static::$schema);
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
                throw new \Exception("Unknown key $name");
            }
            $this->properties[$name] = $schema->getResultFromDb($this->msql, $this->id);
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