<?php

namespace Api\Model\Type;

interface ModelTypeInterface
{
    public function getSqlCreationType(): string;

    public function getSqlType(): string;
    
    public function getGoodTypedValue($value): mixed;
}