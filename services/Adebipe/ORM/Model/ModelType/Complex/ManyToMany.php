<?php

namespace Adebipe\Model\Type;

use Adebipe\Model\Collection;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

/**
 * Many to many relation
 * Himself is the many, the other is the many
 *
 * @package Adebipe\Model\Type
 */
class ManyToMany extends AbstractType implements SqlBasedTypeInterface
{
    /**
     * The object that has this column
     *
     * @var string
     */
    private string $me_object;

    /**
     * The object that is related
     */
    private string $object_type;

    /**
     * Is the first object in the middle table?
     *
     * @var bool
     */
    private bool $is_first;

    /**
     * The name of the table for myself
     *
     * @var string
     */
    private string $named_me;

    /**
     * The name of the table for the other object
     *
     * @var string
     */
    private string $named_object;

    /**
     * The name of the middle table
     */
    private string $middle_table_name;

    /**
     * Many to many relation
     * Himself is the many, the other is the many
     *
     * @param string $me_object
     * @param string $object_type
     * @param bool   $is_first
     */
    public function __construct($me_object, $object_type, bool $is_first)
    {
        $this->me_object = $me_object;
        $this->object_type = $object_type;
        $this->named_me = ORM::class_to_table_name($me_object);
        $this->named_object = ORM::class_to_table_name($object_type);
        $this->is_first = $is_first;
        if ($this->is_first) {
            $this->middle_table_name = $this->named_me . '_' . $this->named_object;
        } else {
            $this->middle_table_name = $this->named_object . '_' . $this->named_me;
        }
        parent::__construct('INT', false, false);
    }

    public function getSqlCreationType(): ?string
    {
        return null;
    }


    public function checkType($value): ?bool
    {
        return null;
    }

    public function getPDOParamType(): ?int
    {
        return null;
    }

    public function getMoreSql(): array
    {
        if (!$this->is_first) {
            return [];
        }
        $table_name = $this->middle_table_name;
        $named_me = $this->named_me;
        $named_object = $this->named_object;
        $first_id = $named_me . '_id';
        $second_id = $named_object . '_id';
        return [
            "now" => [
                'CREATE TABLE ' . $table_name . ' (' .
                    $first_id . ' INT NOT NULL,' .
                    $second_id . ' INT NOT NULL,' .
                    'PRIMARY KEY (' . $first_id . ', ' . $second_id . '))',
            ],
            "after" => [
                `ALTER TABLE ` . $table_name . ` ADD FOREIGN KEY (` . $first_id . `) REFERENCES ` . $named_me . `(id)`,
                `ALTER TABLE ` . $table_name . ` ADD FOREIGN KEY (` . $second_id . `) REFERENCES ` . $named_object . `(id)`,
            ],
        ];
    }

    public function getResultFromDb(MsQl $msql, string $id)
    {
        $query = "SELECT " . $this->named_object . ".* FROM " . $this->named_object .
            " INNER JOIN " . $this->middle_table_name . " ON " . $this->named_object . ".id = " . $this->middle_table_name . "." . $this->named_object . "_id" .
            " WHERE " . $this->middle_table_name . "." . $this->named_me . "_id = " . $id;
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new Collection($data, $this->object_type);
    }

    public function addToDb(MsQl $msql, string $id, object $value): bool
    {
        $query = "INSERT INTO " . $this->middle_table_name . " (" . $this->named_me . "_id, " . $this->named_object . "_id) VALUES (" . $id . ", " . $value->id . ")";
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->get_last_query_success();
    }

    public function deleteToDb(MsQl $msql, string $id, object $value): bool
    {
        $query = "DELETE FROM " . $this->middle_table_name . " WHERE " . $this->named_me . "_id = " . $id . " AND " . $this->named_object . "_id = " . $value->id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->get_last_query_success();
    }
}
