<?php

namespace Api\Model\Type;

use Api\Services\MsQl;
use Api\Services\ORM;

class OneToMany extends AbstractType implements SqlBasedTypeInterface
{
    private $me_object;
    private $object;
    private $relationedBy;
    
    public function __construct($me_object, $object, $relationedBy, bool $not_null = false)
    {
        $this->me_object = $me_object;
        $this->object = $object;
        $this->relationedBy = $relationedBy;
        parent::__construct('INT', $not_null, false);
    }

    public function getSqlCreationType(): ?string
    {
        return null;
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
            " INNER JOIN " . $object_table . " ON " . $me_object_table . ".id = " . $object_table . "." . $this->relationedBy .
            " WHERE " . $me_object_table . ".id = " . $id;
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new $this->object($msql, $data[0]);
    }
}