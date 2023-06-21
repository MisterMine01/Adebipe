<?php

namespace Api\Model\Type;

use Api\Services\ORM;

class OneToMany extends AbstractType
{
    private $object;
    
    public function __construct($object, bool $not_null = false)
    {
        $this->object = $object;
        parent::__construct('INT', $not_null, false);
    }

    public function getSqlCreationType(): ?string
    {
        return null;
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }
}