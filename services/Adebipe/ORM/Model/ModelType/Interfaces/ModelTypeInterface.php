<?php

namespace Adebipe\Model\Type;

/**
 * Interface for types
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface ModelTypeInterface
{
    /**
     * Is the value of this type can be null?
     *
     * @return bool
     */
    public function canBeNull(): bool;

    /**
     * Is the value of this type is auto increment?
     *
     * @return bool
     */
    public function isAutoIncrement(): bool;

    /**
     * Get the PDO type of this type
     *
     * @return int|null
     */
    public function getPDOParamType(): ?int;

    /**
     * Check if the value is of the type of this type
     *
     * @param mixed $value The value to check
     *
     * @return bool|null
     */
    public function checkType(mixed $value): ?bool;

    /**
     * Get the SQL creation type of this type
     * (with NOT NULL and AUTO_INCREMENT)
     *
     * @return string|null
     */
    public function getSqlCreationType(): ?string;

    /**
     * Get the SQL type of this type
     *
     * @return string
     */
    public function getSqlType(): string;

    /**
     * Get more SQL for the construction of the database
     *
     * @return array
     */
    public function getMoreSql(): array;
}
