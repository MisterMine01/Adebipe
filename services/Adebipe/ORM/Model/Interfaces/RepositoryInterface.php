<?php

namespace Adebipe\Model;

/**
 * Repository object of the ORM
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface RepositoryInterface
{
    /**
     * Get the class of the object
     *
     * @param array $data The data to create the object
     *
     * @return object
     */
    public function getObjectClass(array $data): object;

    /**
     * Get the name of the table
     *
     * @return string
     */
    public function getTableName(): string;


    /**
     * Get the name of the model class
     *
     * @return string
     */
    public function getClassName(): string;

    /**
     * Find an object by his id
     *
     * @param int $id The id of the object
     *
     * @return object|null
     */
    public function findOneById(int $id): ?object;

    /**
     * Find an object by conditions
     *
     * @param array      $conditions The conditions to search
     * @param array|null $type       The type of the conditions (PDO::PARAM_*)
     *
     * @return object|null
     */
    public function findOneBy(array $conditions, ?array $type = null): ?object;

    /**
     * Find all objects by conditions
     *
     * @param array      $conditions The conditions to search
     * @param array|null $type       The type of the conditions (PDO::PARAM_*)
     *
     * @return CollectionInterface
     */
    public function findAllBy(array $conditions, ?array $type = null): CollectionInterface;

    /**
     * Find all objects
     *
     * @return CollectionInterface
     */
    public function findAll(): CollectionInterface;

    /**
     * Save an object in the database
     *
     * @param object $object The object to save
     *
     * @return bool
     */
    public function save($object): bool;

    /**
     * Create the table of the repository
     *
     * @return array
     */
    public function createTable(): array;
}
