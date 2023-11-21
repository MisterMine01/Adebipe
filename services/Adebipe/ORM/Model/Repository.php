<?php

namespace Adebipe\Model;

use Adebipe\Model\Type\SqlBasedTypeInterface;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

/**
 * Repository for models
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Repository implements RepositoryInterface
{
    private string $_class_name;
    private string $_table_name;
    private array $_schema;

    private MsQl $_msql;

    /**
     * Repository
     *
     * @param string $class_name The class name of the model
     * @param MsQl   $msql       The MsQl service
     */
    public function __construct($class_name, MsQl $msql)
    {
        $this->_class_name = $class_name;
        $this->_table_name = ORM::classToTableName($class_name);
        $this->_msql = $msql;
        $this->_schema = $this->_class_name::createSchema();
    }

    /**
     * Get the class of the object
     *
     * @param array $data The data to create the object
     *
     * @return object
     */
    public function getObjectClass($data): object
    {
        return new $this->_class_name($data);
    }

    /**
     * Get the name of the table
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->_table_name;
    }

    /**
     * Get the name of the model class
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->_class_name;
    }

    /**
     * Find an object by his id
     *
     * @param int $id The id of the object
     *
     * @return object|null
     */
    public function findOneById(int $id): ?object
    {
        return $this->findOneBy(['id' => $id], [\PDO::PARAM_INT]);
    }

    /**
     * Find an object by conditions
     *
     * @param array      $conditions The conditions to search
     * @param array|null $type       The type of the conditions (PDO::PARAM_*)
     *
     * @return object|null
     */
    public function findOneBy(array $conditions, ?array $type = null): ?object
    {
        $query = "SELECT * FROM " . $this->_table_name . " WHERE ";
        $query .= implode(
            " AND ",
            array_map(
                function ($key) {
                    return "$key = ?";
                },
                array_keys($conditions)
            )
        );
        $statement = $this->_msql->prepare($query);
        if ($type === null) {
            $type = array_fill(0, count($conditions), \PDO::PARAM_STR);
        }
        $result = $this->_msql->execute($statement, array_values($conditions), $type);
        if ($result === null) {
            return null;
        }
        if (count($result) === 0) {
            return null;
        }
        return new $this->_class_name($result[0]);
    }

    /**
     * Find all objects by conditions
     *
     * @param array      $conditions The conditions to search
     * @param array|null $type       The type of the conditions (PDO::PARAM_*)
     *
     * @return CollectionInterface
     */
    public function findAllBy(array $conditions, ?array $type = null): Collection
    {
        $query = "SELECT * FROM ? WHERE ";
        $query .= implode(
            " AND ",
            array_map(
                function ($key) {
                    return "$key = ?";
                },
                array_keys($conditions)
            )
        );
        $statement = $this->_msql->prepare($query);
        if ($type === null) {
            $type = array_fill(0, count($conditions), \PDO::PARAM_STR);
        }
        $result = $this->_msql->execute($statement, array_values($conditions), array_merge([\PDO::PARAM_STR], $type));
        return new Collection($result, $this->_class_name);
    }

    /**
     * Find all objects
     *
     * @return CollectionInterface
     */
    public function findAll(): Collection
    {
        $query = "SELECT * FROM " . $this->_table_name;
        $result = $this->_msql->prepare($query);
        $sql = $this->_msql->execute($result);
        return new Collection($sql, $this->_class_name);
    }

    /**
     * Save an object in the database
     *
     * @param object $object The object to save
     *
     * @return bool
     */
    public function save(&$object): bool
    {
        if (!is_a($object, $this->_class_name)) {
            throw new \Exception("You can't save object of class " . get_class($object) . " as " . $this->_class_name);
        }

        $keys = [];
        $values = [];
        $param_type = [];

        foreach ($this->_schema as $key => $type) {
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

        $sql = "INSERT INTO " . $this->_table_name;
        $sql .= " (";
        $sql .= implode(", ", $keys);
        $sql .= ") VALUES (";
        $sql .= implode(", ", array_fill(0, count($keys), "?"));
        $sql .= ") ON DUPLICATE KEY UPDATE ";
        $sql .= implode(
            ", ",
            array_map(
                function ($key) {
                    return "$key = VALUES($key)";
                },
                $keys
            )
        );
        $result = $this->_msql->prepare($sql);
        $result = $this->_msql->execute($result, array_values($values));
        $succeed = $this->_msql->getLastQuerySuccess();
        if ($succeed === false) {
            return false;
        }
        $sql = 
    }

    /**
     * Create the table of the repository
     *
     * @return array
     */
    public function createTable(): array
    {
        $query = "CREATE TABLE " . $this->_table_name . " (";
        $for_now = [];
        $for_after = [];
        foreach ($this->_schema as $column_name => $column_type) {
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
        $result = $this->_msql->prepare($query);
        $this->_msql->execute($result);
        foreach ($for_now as $sql) {
            $result = $this->_msql->prepare($sql);
            $this->_msql->execute($result);
        }
        return $for_after;
    }
}
