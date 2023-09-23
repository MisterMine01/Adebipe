<?php

namespace Adebipe\Model\Type;

use Adebipe\Model\Collection;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

/**
 * One to many relation
 * Himself is the one, the other is the many
 *
 * @package Adebipe\Model\Type
 */
class OneToMany extends AbstractType implements SqlBasedTypeInterface
{
    /**
     * The object that has the relation
     *
     * @var string
     */
    private string $me_object;

    /**
     * The object that is related
     *
     * @var string
     */
    private string $object;

    /**
     * The column that is related  (in the other object)
     *
     * @var string
     */
    private string $relationedBy;

    /**
     * One to many relation
     * Himself is the one, the other is the many
     *
     * @param  string $me_object
     * @param  string $object
     * @param  string $relationedBy
     * @return void
     */
    public function __construct(string $me_object, string $object, string $relationedBy)
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

    public function addToDb(MsQl $msql, string $id, object $value): bool
    {
        $object_table = ORM::class_to_table_name($this->object);
        $query = "UPDATE " . $object_table . " SET " . $this->relationedBy . " = " . $id . " WHERE id = " . $value->id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->getLastQuerySuccess();
    }

    public function deleteToDb(MsQl $msql, string $id, object $value): bool
    {
        $object_table = ORM::class_to_table_name($this->object);
        $query = "UPDATE " . $object_table . " SET " . $this->relationedBy . " = NULL WHERE id = " . $value->id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->getLastQuerySuccess();
    }
}
