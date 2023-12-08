<?php

namespace Adebipe\Model\Type;

use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

/**
 * Many to one relation
 * Himself is the many, the other is the one
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ManyToOne extends AbstractType implements SqlBasedTypeInterface
{
    /**
     * The object that has the relation
     *
     * @var string
     */
    private $_me_object;

    /**
     * The column that is related (this column)
     *
     * @var string
     */
    private string $_relationedBy;

    /**
     * The object that is related
     */
    private string $_object;

    /**
     * Many to one relation
     * Himself is the many, the other is the one
     *
     * @param string $me_object    This object
     * @param string $relationedBy The column that is related (this column)
     * @param string $object       The object that is related
     */
    public function __construct($me_object, $relationedBy, $object)
    {
        $this->_me_object = $me_object;
        $this->_relationedBy = $relationedBy;
        $this->_object = $object;
        parent::__construct('INT', false, false);
    }

    /**
     * Get SQL for the construction of the database
     *
     * @return array
     */
    public function getMoreSql(): array
    {
        return [
            "after" => [
                "ALTER TABLE " . ORM::classToTableName($this->_me_object) .
                    " ADD FOREIGN KEY (" . $this->_relationedBy . ") REFERENCES " .
                    ORM::classToTableName($this->_object) . "(id)"
            ]
        ];
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
     * Get the result from the database
     *
     * @param MsQl   $msql The connection to the database
     * @param string $id   The id of the object
     *
     * @return Object|null
     */
    public function getResultFromDb(MsQl $msql, string $id)
    {
        $object_table = ORM::classToTableName($this->_object);
        $me_object_table = ORM::classToTableName($this->_me_object);
        $query = "SELECT " . $object_table . ".* FROM " . $me_object_table .
            " INNER JOIN " . $object_table . " ON " .
            $me_object_table . "." . $this->_relationedBy . " = " . $object_table . ".id" .
            " WHERE " . $me_object_table . ".id = " . $id;
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new $this->_object($data[0]);
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
        $me_object_table = ORM::classToTableName($this->_me_object);
        $query = "UPDATE " . $me_object_table . " SET " . $this->_relationedBy . " = " . $value->id .
            " WHERE id = " . $id;
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
        $me_object_table = ORM::classToTableName($this->_me_object);
        $query = "UPDATE " . $me_object_table . " SET " . $this->_relationedBy . " = NULL WHERE id = " . $id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->getLastQuerySuccess();
    }
}
