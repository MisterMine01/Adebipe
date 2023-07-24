<?php

use Adebipe\Model\Collection;

interface RepositoryInterface
{
    function getObjectClass($data): object;

    function getTableName(): string;

    function findOneById(int $id): ?object;

    function findOneBy(array $conditions, ?array $type = null) : ?object;

    function findAllBy(array $conditions, ?array $type = null) : Collection;

    function findAll(): Collection;

    function save($object): bool;

    function create_table(): array;
}