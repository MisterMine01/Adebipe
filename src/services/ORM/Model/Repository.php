<?php

namespace Api\Model;

use Api\Services\MsQl;

class Repository
{
    private string $class_name;
    private string $table_name;
    private array $schema;

    private MsQl $msql;

    public function __construct($class_name, MsQl $msql)
    {
        $this->class_name = $class_name;
        $this->table_name = strtolower(substr($class_name, strrpos($class_name, '\\') + 1));
        $this->msql = $msql;
        $this->schema = $this->class_name::$schema;
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }


    public function findById(int $id)
    {
        $query = "SELECT * FROM ? WHERE id = ?";
        $result = $this->msql->prepare($query);
        return $this->msql->execute($result, [$this->table_name, $id]);
    }

    public function findAll(): Collection
    {
        $query = "SELECT * FROM ?";
        $result = $this->msql->prepare($query);
        $sql = $this->msql->execute($result, [$this->table_name]);
        return new Collection($sql, $this->class_name);
    }

    public function create_table(): void
    {
        $query = "CREATE TABLE " . $this->table_name . " (";
        foreach ($this->schema as $column_name => $column_type) {
            $query .= $column_name . " " . $column_type->getSqlCreationType() . ', ';
        }
        $query .= 'PRIMARY KEY (id)';
        $query .= ')';
        $result = $this->msql->prepare($query);
        $this->msql->execute($result);
        
    }
}