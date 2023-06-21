<?php

namespace Api\Model\Type;

use Api\Services\MsQl;
use Api\Services\ORM;

class ManyToOne extends AbstractType implements SqlBasedTypeInterface
{
    private $me_object;
    private $relationedBy;
    private $object;

    public function __construct($me_object, $relationedBy, $object, bool $not_null = false)
    {
        $this->me_object = $me_object;
        $this->relationedBy = $relationedBy;
        $this->object = $object;
        parent::__construct('INT', $not_null, false);
    }

    public function getMoreSql(): array
    {
        return [
            "after" => [
                "ALTER TABLE " . ORM::class_to_table_name($this->me_object) . 
                " ADD FOREIGN KEY (" . $this->relationedBy . ") REFERENCES " . ORM::class_to_table_name($this->object) . "(id)"
            ]
        ];
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }

    public function getResultFromDb(MsQl $msql, string $id)
    {
        $object_table = ORM::class_to_table_name($this->object);
        $me_object_table = ORM::class_to_table_name($this->me_object);
        $query = "SELECT " . $object_table . ".* FROM " . $me_object_table .
            " INNER JOIN " . $object_table . " ON " . $me_object_table . "." . $this->relationedBy . " = " . $object_table . ".id" .
            " WHERE " . $me_object_table . ".id = " . $id;
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new $this->object($msql, $data[0]);
    }
}