<?php

namespace Api\Model\Type;


class IntType extends AbstractType
{
    public function __construct(bool $not_null = false, bool $auto_increment = false)
    {
        parent::__construct('INT', $not_null, $auto_increment);
    }

    public function getGoodTypedValue($value): mixed
    {
        return (int) $value;
    }
}