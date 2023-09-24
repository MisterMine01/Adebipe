<?php

namespace Adebipe\Model\Type;

use Adebipe\Model\Collection;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

/**
 * One to many relation
 * Himself is the one, the other is the many
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class OneToMany extends AbstractType implements SqlBasedTypeInterface
{
    /**
     * The object that has the relation
     *
     * @var string
     */
    private string $_me_object;

    /**
     * The object that is related
     *
     * @var string
     */
    private string $_object;

    /**
     * The column that is related  (in the other object)
     *
     * @var string
     */
    private string $_relationedBy;

    /**
     * One to many relation
     * Himself is the one, the other is the many
     *
     * @param string $me_object    This object
     * @param string $object       The object that is related
     * @param string $relationedBy The column that is related (in the other object)
     */
    public function __construct(string $me_object, string $object, string $relationedBy)
    {
        $this->_me_object = $me_object;
        $this->_object = $object;
        $this->_relationedBy = $relationedBy;
        parent::__construct('INT', false, false);
    }

    /**
     * Check the type of the value
     *
     * @param mixed $value The value to check
     *
     * @return bool|null
     */
    public function checkType(mixed $value): ?bool
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
     * Get the sql creation type
     *
     * @return string|null
     */
    public function getSqlCreationType(): ?string
    {
        return null;
    }

    /**
     * Get the result from the database
     *
     * @param MsQl   $msql The connection to the database
     * @param string $id   The id of the object
     *
     * @return Collection
     */
    public function getResultFromDb(MsQl $msql, string $id)
    {
        $object_table = ORM::classToTableName($this->_object);
        $me_object_table = ORM::classToTableName($this->_me_object);
        $query = "SELECT " . $object_table . ".* FROM " . $me_object_table .
            " INNER JOIN " . $object_table . " ON " .
            $me_object_table . ".id = " . $object_table . "." . $this->_relationedBy .
            " WHERE " . $me_object_table . ".id = " . $id;
        $result = $msql->prepare($query);
        $data = $msql->execute($result);
        return new Collection($data, $this->_object);
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
        $object_table = ORM::classToTableName($this->_object);
        $query = "UPDATE " . $object_table . " SET " . $this->_relationedBy . " = " . $id . " WHERE id = " . $value->id;
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
        $object_table = ORM::classToTableName($this->_object);
        $query = "UPDATE " . $object_table . " SET " . $this->_relationedBy . " = NULL WHERE id = " . $value->id;
        $result = $msql->prepare($query);
        $msql->execute($result);
        return $msql->getLastQuerySuccess();
    }
}
