<?php

namespace Api\Model\Type;


class DateTimeType extends AbstractType
{
    public function __construct(bool $not_null = false)
    {
        parent::__construct('DATETIME', $not_null, false);
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }
}