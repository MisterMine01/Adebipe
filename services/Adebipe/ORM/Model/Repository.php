<?php

namespace Adebipe\Model;

use Adebipe\Model\Type\SqlBasedTypeInterface;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

class Repository implements RepositoryInterface
{
    private string $class_name;
    private string $table_name;
    private array $schema;

    private MsQl $msql;

    public function __construct($class_name, MsQl $msql)
    {
        $this->class_name = $class_name;
        $this->table_name = ORM::class_to_table_name($class_name);
        $this->msql = $msql;
        $this->schema = $this->class_name::createSchema();
    }

    public function getObjectClass($data): object
    {
        return new $this->class_name($data);
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function findOneById(int $id): ?object
    {
        return $this->findOneBy(['id' => $id], [\PDO::PARAM_INT]);
    }

    public function findOneBy(array $conditions, ?array $type = null) : ?object
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ";
        $query .= implode(
            " AND ", array_map(
                function ($key) {
                    return "$key = ?";
                }, array_keys($conditions)
            )
        );
        $statement = $this->msql->prepare($query);
        if ($type === null) {
            $type = array_fill(0, count($conditions), \PDO::PARAM_STR);
        }
        $result = $this->msql->execute($statement, array_values($conditions), $type);
        if ($result === null) {
            return null;
        }
        if (count($result) === 0) {
            return null;
        }
        return new $this->class_name($result[0]);
    }

    public function findAllBy(array $conditions, ?array $type = null) : Collection
    {
        $query = "SELECT * FROM ? WHERE ";
        $query .= implode(
            " AND ", array_map(
                function ($key) {
                    return "$key = ?";
                }, array_keys($conditions)
            )
        );
        $statement = $this->msql->prepare($query);
        if ($type === null) {
            $type = array_fill(0, count($conditions), \PDO::PARAM_STR);
        }
        $result = $this->msql->execute($statement, array_values($conditions), array_merge([\PDO::PARAM_STR], $type));
        return new Collection($result, $this->class_name);
    }

    public function findAll(): Collection
    {
        $query = "SELECT * FROM " . $this->table_name;
        $result = $this->msql->prepare($query);
        $sql = $this->msql->execute($result);
        return new Collection($sql, $this->class_name);
    }

    public function save($object): bool
    {
        if (!is_a($object, $this->class_name)) {
            throw new \Exception("You can't save object of class " . get_class($object) . " as " . $this->class_name);
        }

        $keys = [];
        $values = [];
        $param_type = [];

        foreach ($this->schema as $key => $type) {
            $model_type = $type;
            if (is_subclass_of($type, SqlBasedTypeInterface::class)) {
                continue;
            }
            if (!$model_type->canBeNull() && $object->$key === null) {
                if ($key === 'created_at' || $key === 'updated_at') {
                    $keys[] = $key;
                    $values[] = date("Y-m-d H:i:s");
                    $param_type[] = \PDO::PARAM_STR;
                    continue;
                }
                if ($model_type->isAutoIncrement()) {
                    continue;
                }
                throw new \Exception("You can't save object with null value of $key");
            }
            if ($model_type->canBeNull() && $object->$key === null) {
                continue;
            }
            $check_type = $model_type->checkType($object->$key);
            if ($check_type === false) {
                throw new \Exception("You can't save object with wrong type of $key");
            }
            if ($check_type === null) {
                continue;
            }
            if ($key === 'updated_at') {
                $keys[] = $key;
                $values[] = date("Y-m-d H:i:s");
                $param_type[] = \PDO::PARAM_STR;
                continue;
            }
            $keys[] = $key;
            $values[] = $object->$key;
            $param_type[] = $model_type->getPDOParamType();
        }

        $sql = "INSERT INTO " . $this->table_name . " (";
        $sql .= implode(", ", $keys);
        $sql .= ") VALUES (";
        $sql .= implode(", ", array_fill(0, count($keys), "?"));
        $sql .= ") ON DUPLICATE KEY UPDATE ";
        $sql .= implode(
            ", ", array_map(
                function ($key) {
                    return "$key = VALUES($key)";
                }, $keys
            )
        );
        $result = $this->msql->prepare($sql);
        $this->msql->execute($result, array_values($values));
        return $this->msql->get_last_query_success();
    }

    public function create_table(): array
    {
        $query = "CREATE TABLE " . $this->table_name . " (";
        $for_now = [];
        $for_after = [];
        foreach ($this->schema as $column_name => $column_type) {
            $type = $column_type->getSqlCreationType();
            $type_more_sql = $column_type->getMoreSql();
            if (in_array("now", array_keys($type_more_sql))) {
                $for_now = array_merge($for_now, $type_more_sql["now"]);
            }
            if (in_array("after", array_keys($type_more_sql))) {
                $for_after = array_merge($for_after, $type_more_sql["after"]);
            }
            if ($type === null) {
                continue;
            }
            $query .= $column_name . " " . $type . ', ';
        }
        $query .= 'PRIMARY KEY (id)';
        $query .= ')';
        $result = $this->msql->prepare($query);
        $this->msql->execute($result);
        foreach ($for_now as $sql) {
            $result = $this->msql->prepare($sql);
            $this->msql->execute($result);
        }
        return $for_after;
    }
}
