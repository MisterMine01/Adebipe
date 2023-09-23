<?php

namespace Adebipe\Model\Type;

class IntType extends AbstractType
{
    public function __construct(bool $not_null = false, bool $auto_increment = false)
    {
        parent::__construct('INT', $not_null, $auto_increment);
    }

    public function checkType(mixed $value): ?bool
    {
        return is_int($value);
    }

    public function getPDOParamType(): ?int
    {
        return \PDO::PARAM_INT;
    }
}
