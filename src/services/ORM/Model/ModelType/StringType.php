<?php

namespace Api\Model\Type;


class StringType extends AbstractType
{
    public function __construct(bool $not_null = false)
    {
        parent::__construct('INT', $not_null, false);
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }
}