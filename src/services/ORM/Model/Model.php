<?php

namespace Api\Model;

abstract class Model {
    private $table_name;

    public function __construct()
    {
        echo get_class($this) . "<br>";
        fwrite(STDOUT, get_class($this) . "\n");
    }

    public function getTableName(): string {
        return static::$table_name;
    }

    public function getSchema(): array {
        return static::$schema;
    }

    public function getKey(): array {
        return array_keys(static::$schema);
    }

    public function __get(string $name)
    {
        return $this->{$name};
    }

    public function __set(string $name, $value)
    {
        $this->{$name} = $value;
    }

    public function __isset(string $name)
    {
        return isset($this->{$name});
    }
}