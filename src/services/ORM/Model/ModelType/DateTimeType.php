<?php

namespace Adebipe\Model\Type;

use DateTime;

class DateTimeType extends AbstractType
{
    public function __construct(bool $not_null = false)
    {
        parent::__construct('DATETIME', $not_null, false);
    }


    public function checkType(mixed $value): ?bool
    {
        if (is_string($value)) {
            $test = DateTime::createFromFormat('Y-m-d H:i:s', $value);
            return $test !== false;
        }
        return false;
    }

    public function getPDOParamType(): ?int
    {
        return \PDO::PARAM_STR;
    }
}
