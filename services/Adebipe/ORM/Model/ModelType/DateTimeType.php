<?php

namespace Adebipe\Model\Type;

use DateTime;

/**
 * DateTime type
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class DateTimeType extends AbstractType
{
    /**
     * DateTime type
     *
     * @param bool $not_null If the column can be null
     */
    public function __construct(bool $not_null = false)
    {
        parent::__construct('DATETIME', $not_null, false);
    }

    /**
     * Check if the value is of the type of this type
     *
     * @param mixed $value The value to check
     *
     * @return bool|null
     */
    public function checkType(mixed $value): ?bool
    {
        if (is_string($value)) {
            $test = DateTime::createFromFormat('Y-m-d H:i:s', $value);
            return $test !== false;
        }
        return false;
    }

    /**
     * Get the PDO type of this type
     *
     * @return int|null
     */
    public function getPDOParamType(): ?int
    {
        return \PDO::PARAM_STR;
    }
}
