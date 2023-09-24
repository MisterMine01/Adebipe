<?php

namespace Adebipe\Model\Type;

/**
 * Int type
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class IntType extends AbstractType
{
    /**
     * Int type
     *
     * @param bool $not_null       If the column can be null
     * @param bool $auto_increment If the column is auto increment
     */
    public function __construct(bool $not_null = false, bool $auto_increment = false)
    {
        parent::__construct('INT', $not_null, $auto_increment);
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
        return is_int($value);
    }

    /**
     * Get the PDO type of this type
     *
     * @return int|null
     */
    public function getPDOParamType(): ?int
    {
        return \PDO::PARAM_INT;
    }
}
