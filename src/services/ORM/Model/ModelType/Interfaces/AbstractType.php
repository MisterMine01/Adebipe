<?php

namespace Api\Model\Type;


abstract class AbstractType implements ModelTypeInterface
{
    public string $type;

    public bool $not_null = false;

    public bool $auto_increment = false;

    public function __construct(string $type, bool $not_null = false, bool $auto_increment = false)
    {
        $this->type = $type;
        $this->not_null = $not_null;
        $this->auto_increment = $auto_increment;
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