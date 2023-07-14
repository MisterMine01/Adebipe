<?php

namespace Adebipe\Model\Type;

use Adebipe\Model\Collection;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

class OneToMany extends AbstractType implements SqlBasedTypeInterface
{
    private $me_object;
    private $object;
    private $relationedBy;
    
    public function __construct($me_object, $object, $relationedBy)
    {
        $this->me_object = $me_object;
        $this->object = $object;
        $this->relationedBy = $relationedBy;
        parent::__construct('INT', false, false);
    }


    public function checkType(mixed $value): ?bool
    {
        return null;
    }

    public function getPDOParamType(): ?int
    {
        return null;
    }

    public function getSqlCreationType(): ?string
    {
        return null;
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
        return new Collection($data, $this->object);
    }
}