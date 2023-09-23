<?php

namespace Adebipe\Model\Type;

use Adebipe\Model\Collection;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

/**
 * Many to many relation
 * Himself is the many, the other is the many
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ManyToMany extends AbstractType implements SqlBasedTypeInterface
{
    /**
     * The object that has this column
     *
     * @var string
     */
    private string $_me_object;

    /**
     * The object that is related
     */
    private string $_object_type;

    /**
     * Is the first object in the middle table?
     *
     * @var bool
     */
    private bool $_is_first;

    /**
     * The name of the table for myself
     *
     * @var string
     */
    private string $_named_me;

    /**
     * The name of the table for the other object
     *
     * @var string
     */
    private string $_named_object;

    /**
     * The name of the middle table
     */
    private string $_middle_table_name;

    /**
     * Many to many relation
     * Himself is the many, the other is the many
     *
     * @param string $me_object   The object that has this column
     * @param string $object_type The object that is related
     * @param bool   $is_first    Is the first object in the middle table?
     */
    public function __construct($me_object, $object_type, bool $is_first)
    {
        $this->_me_object = $me_object;
        $this->_object_type = $object_type;
        $this->_named_me = ORM::class_to_table_name($me_object);
        $this->_named_object = ORM::class_to_table_name($object_type);
        $this->_is_first = $is_first;
        if ($this->_is_first) {
            $this->_middle_table_name = $this->_named_me . '_' . $this->_named_object;
        } else {
            $this->_middle_table_name = $this->_named_object . '_' . $this->_named_me;
        }
        parent::__construct('INT', false, false);
    }

    /**
     * Get The sql creation type
     *
     * @return string|null
     */
    public function getSqlCreationType(): ?string
    {
        return null;
    }


    /**
     * Check the type of the value
     *
     * @param mixed $value The value to check
     *
     * @return bool|null
     */
    public function checkType($value): ?bool
    {
        return null;
    }

    /**
     * Get the PDO type of this type
     *
     * @return int|null
     */
    public function getPDOParamType(): ?int
    {
        return null;
    }

    /**
     * Get SQL for the construction of the database
     *
     * @return array
     */
    public function getMoreSql(): array
    {
        if (!$this->_is_first) {
            return [];
        }
        $table_name = $this->_middle_table_name;
        $named_me = $this->_named_me;
        $named_obj = $this->_named_object;
        $first_id = $named_me . '_id';
        $sec_id = $named_obj . '_id';
        return [
            "now" => [
                'CREATE TABLE ' . $table_name . ' (' .
                    $first_id . ' INT NOT NULL,' .
                    $sec_id . ' INT NOT NULL,' .
                    'PRIMARY KEY (' . $first_id . ', ' . $sec_id . '))',
            ],
            "after" => [
                `ALTER TABLE ` . $table_name . ` ADD FOREIGN KEY (` . $first_id . `) REFERENCES ` . $named_me . `(id)`,
                `ALTER TABLE ` . $table_name . ` ADD FOREIGN KEY (` . $sec_id . `) REFERENCES ` . $named_obj . `(id)`,
            ],
        ];
    }

    /**
     * Get the result from the database
     *
     * @param MsQl   $msql The connection to the database
     * @param string $id   The id of the object
     *
     * @return Collection
     */
    public function getResultFromDb(MsQl $msql, string $id): Collection
    {
        $query = ("SELECT " . $this->_named_object . ".*" .
            " FROM " . $this->_named_object .
            " INNER JOIN " . $this->_middle_table_name . " ON " .
            $this->_named_object . ".id = " . $this->_middle_table_name . "." . $this->_named_object . "_id" .
            " WHERE " . $this->_middle_table_name . "." . $this->_named_me . "_id = " . $id
        );
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new Collection($data, $this->_object_type);
    }

    /**
     * Add the value to the database
     *
     * @param MsQl   $msql  The database connection
     * @param string $id    The id of the object
     * @param object $value The value to add
     *
     * @return bool
     */
    public function addToDb(MsQl $msql, string $id, object $value): bool
    {
        $query = ("INSERT INTO " . $this->_middle_table_name .
            " (" . $this->_named_me . "_id, " . $this->_named_object . "_id) VALUES" .
            " (" . $id . ", " . $value->id . ")"
        );
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->getLastQuerySuccess();
    }

    /**
     * Delete the relation to the database
     *
     * @param MsQl   $msql  The database connection
     * @param string $id    The id of the object
     * @param object $value The value to delete
     *
     * @return bool
     */
    public function deleteToDb(MsQl $msql, string $id, object $value): bool
    {
        $query = "DELETE FROM " . $this->_middle_table_name . " WHERE " . $this->_named_me . "_id = " . $id .
            " AND " . $this->_named_object . "_id = " . $value->id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->getLastQuerySuccess();
    }
}
