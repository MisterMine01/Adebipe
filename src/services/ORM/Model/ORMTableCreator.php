<?php


namespace Adebipe\Model;


class ORMTableCreator
{

    public array $database_schema = [];

    public function create_model(string $object_class)
    {
        $this->database_schema[$object_class] = $object_class;
    }

    public function getSchema(): array
    {
        return $this->database_schema;
    }
}
