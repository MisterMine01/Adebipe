<?php

namespace Adebipe\Model\Type;

use Adebipe\Services\MsQl;

/**
 * Interface for types that are based with SQL (relation)
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface SqlBasedTypeInterface
{
    /**
     * Get the result from the database for this type
     *
     * @param MsQl   $msql The database connection
     * @param string $id   The id of the object
     *
     * @return object|null
     */
    public function getResultFromDb(MsQl $msql, string $id);

    /**
     * Add the value to the database
     *
     * @param MsQl   $msql  The database connection
     * @param string $id    The id of the object
     * @param object $value The value to add
     *
     * @return bool
     */
    public function addToDb(MsQl $msql, string $id, object $value): bool;

    /**
     * Delete the relation to the database
     *
     * @param MsQl   $msql  The database connection
     * @param string $id    The id of the object
     * @param object $value The value to delete
     *
     * @return bool
     */
    public function deleteToDb(MsQl $msql, string $id, object $value): bool;
}
