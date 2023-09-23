<?php

namespace Adebipe\Model\Type;

/**
 * Abstract class for types
 *
 * @package Adebipe\Model\Type
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
     * @param  string $type
     * @param  bool   $not_null
     * @param  bool   $auto_increment
     * @return void
     */
    public function __construct(string $type, bool $not_null = false, bool $auto_increment = false)
    {
        $this->type = $type;
        $this->not_null = $not_null;
        $this->auto_increment = $auto_increment;
    }

    public function canBeNull(): bool
    {
        return !$this->not_null;
    }

    public function isAutoIncrement(): bool
    {
        return $this->auto_increment;
    }

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

    public function getMoreSql(): array
    {
        return [];
    }

    public function getSqlType(): string
    {
        return $this->type;
    }
}
