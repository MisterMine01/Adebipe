<?php


namespace Api\Model;


class ORMTableCreator {

    public array $database_schema = [];

    public function create_model(string $object_class) {
        $database_schema[$object_class] = $object_class::$schema;
    }
}