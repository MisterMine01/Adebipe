<?php

namespace Api\Model\Type;


class StringType extends AbstractType
{
    public function __construct(int $size, bool $not_null = false)
    {
        parent::__construct('VARCHAR(' . $size . ')', $not_null, false);
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }
}