<?php

namespace Adebipe\Model\Type;

/**
 * Abstract class for types
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
abstract class AbstractType implements ModelTypeInterface
{
    /**
     * The type of the column
     *
     * @var string
     */
    public string $type;

    /**
     * If the column can be null
     *
     * @var bool
     */
    public bool $not_null = false;

    /**
     * If the column is auto increment
     *
     * @var bool
     */
    public bool $auto_increment = false;

    /**
     * Abstract class for types
     *
     * @param string $type           The type of the column
     * @param bool   $not_null       If the column can be null
     * @param bool   $auto_increment If the column is auto increment
     */
    public function __construct(string $type, bool $not_null = false, bool $auto_increment = false)
    {
        $this->type = $type;
        $this->not_null = $not_null;
        $this->auto_increment = $auto_increment;
    }

    /**
     * Is the value of this type can be null?
     *
     * @return bool
     */
    public function canBeNull(): bool
    {
        return !$this->not_null;
    }

    /**
     * Is the value of this type is auto increment?
     *
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->auto_increment;
    }

    /**
     * Get the SQL creation type of this type
     * (with NOT NULL and AUTO_INCREMENT)
     *
     * @return string|null
     */
    public function getSqlCreationType(): ?string
    {
        $sql = $this->type;

        if ($this->not_null) {
            $sql .= ' NOT NULL';
        }

        if ($this->auto_increment) {
            $sql .= ' AUTO_INCREMENT';
        }

        return $sql;
    }

    /**
     * Get more SQL for the construction of the database
     *
     * @return array
     */
    public function getMoreSql(): array
    {
        return [];
    }

    /**
     * Get the SQL type of this type
     *
     * @return string
     */
    public function getSqlType(): string
    {
        return $this->type;
    }
}
