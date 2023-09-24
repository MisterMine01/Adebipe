<?php

namespace Adebipe\Model\Type;

/**
 * String type
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class StringType extends AbstractType
{
    protected int $size;

    /**
     * StringType
     *
     * @param int  $size     The size of the string
     * @param bool $not_null If the column can be null
     */
    public function __construct(int $size, bool $not_null = false)
    {
        parent::__construct('VARCHAR(' . $size . ')', $not_null, false);
        $this->size = $size;
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
        return is_string($value) && strlen($value) <= $this->size;
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
