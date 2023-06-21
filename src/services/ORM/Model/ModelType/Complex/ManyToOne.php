<?php

namespace Api\Model\Type;

use Api\Services\ORM;

class ManyToOne extends AbstractType
{
    private $me_object;
    private $relationedBy;
    private $object;

    public function __construct($me_object, $relationedBy, $object, bool $not_null = false)
    {
        $this->me_object = $me_object;
        $this->relationedBy = $relationedBy;
        $this->object = $object;
        parent::__construct('INT', $not_null, false);
    }

    public function getMoreSql(): array
    {
        return [
            "after" => [
                "ALTER TABLE " . ORM::class_to_table_name($this->me_object) . 
                " ADD FOREIGN KEY (" . $this->relationedBy . ") REFERENCES " . ORM::class_to_table_name($this->object) . "(id)"
            ]
        ];
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }
}