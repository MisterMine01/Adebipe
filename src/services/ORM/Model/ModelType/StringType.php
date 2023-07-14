<?php

namespace Api\Model\Type;


class StringType extends AbstractType
{
    protected int $size;

    public function __construct(int $size, bool $not_null = false)
    {
        parent::__construct('VARCHAR(' . $size . ')', $not_null, false);
        $this->size = $size;
    }

    public function checkType(mixed $value): ?bool
    {
        return is_string($value) && strlen($value) <= $this->size;
    }

    public function getPDOParamType(): ?int
    {
        return \PDO::PARAM_STR;
    }
}