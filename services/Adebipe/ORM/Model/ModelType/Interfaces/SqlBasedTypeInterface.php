<?php

namespace Adebipe\Model\Type;

use Adebipe\Services\MsQl;

/**
 * Interface for types that are based with SQL (relation)
 * @package Adebipe\Model\Type
 */
interface SqlBasedTypeInterface
{
    /**
     * Get the result from the database for this type
     * @param MsQl $msql
     * @param string $id
     * @return object|null
     */
    public function getResultFromDb(MsQl $msql, string $id);

    /**
     * Add the value to the database
     * @param MsQl $msql
     * @param string $id
     * @param object $value
     * @return bool
     */
    public function addToDb(MsQl $msql, string $id, object $value): bool;

    /**
     * Delete the relation to the database
     * @param MsQl $msql
     * @param string $id
     * @param object $value
     * @return bool
     */
    public function deleteToDb(MsQl $msql, string $id, object $value): bool;
}