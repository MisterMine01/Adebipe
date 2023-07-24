<?php

namespace Adebipe\Model\Type;

use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

class ManyToOne extends AbstractType implements SqlBasedTypeInterface
{
    private $me_object;
    private $relationedBy;
    private $object;

    public function __construct($me_object, $relationedBy, $object)
    {
        $this->me_object = $me_object;
        $this->relationedBy = $relationedBy;
        $this->object = $object;
        parent::__construct('INT', false, false);
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

    public function getResultFromDb(MsQl $msql, string $id)
    {
        $object_table = ORM::class_to_table_name($this->object);
        $me_object_table = ORM::class_to_table_name($this->me_object);
        $query = "SELECT " . $object_table . ".* FROM " . $me_object_table .
            " INNER JOIN " . $object_table . " ON " . $me_object_table . "." . $this->relationedBy . " = " . $object_table . ".id" .
            " WHERE " . $me_object_table . ".id = " . $id;
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new $this->object($data[0]);
    }

    public function addToDb(MsQl $msql, string $id, object $value): bool
    {
        $me_object_table = ORM::class_to_table_name($this->me_object);
        $query = "UPDATE " . $me_object_table . " SET " . $this->relationedBy . " = " . $value->id . " WHERE id = " . $id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->get_last_query_success();
    }

    public function deleteToDb(MsQl $msql, string $id, object $value): bool
    {
        $me_object_table = ORM::class_to_table_name($this->me_object);
        $query = "UPDATE " . $me_object_table . " SET " . $this->relationedBy . " = NULL WHERE id = " . $id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->get_last_query_success();
    }



    public function checkType($value): ?bool
    {
        return null;
    }

    public function getPDOParamType(): ?int
    {
        return null;
    }
}