<?php

namespace Api\Model\Type;

class ManyToMany extends AbstractType
{
    private $me_object;
    private string $object_type;
    private bool $is_first;
    private string $named_me;
    private string $named_object;
    private string $middle_table_name;

    public function __construct($me_object, $object_type, bool $is_first)
    {
        $this->me_object = $me_object;
        $this->object_type = $object_type;
        $this->named_me = strtolower(substr($me_object, strrpos($me_object, '\\') + 1));
        $this->named_object = strtolower(substr($object_type, strrpos($object_type, '\\') + 1));
        $this->is_first = $is_first;
        if ($this->is_first) {
            $this->middle_table_name = $this->named_me . '_' . $this->named_object;
        } else {
            $this->middle_table_name = $this->named_object . '_' . $this->named_me;
        }
        parent::__construct('INT', false, false);
    }

    public function getSqlCreationType(): ?string
    {
        return null;
    }

    public function getMoreSql(): array
    {
        if (!$this->is_first) {
            return [];
        }
        $table_name = $this->middle_table_name;
        $named_me = $this->named_me;
        $named_object = $this->named_object;
        $first_id = $named_me . '_id';
        $second_id = $named_object . '_id';
        return [
            "now" => [
                'CREATE TABLE ' . $table_name . ' (' .
                    $first_id . ' INT NOT NULL,' .
                    $second_id . ' INT NOT NULL,' .
                    'PRIMARY KEY (' . $first_id . ', ' . $second_id . '))',
            ],
            "after" => [
                `ALTER TABLE ` . $table_name . ` ADD FOREIGN KEY (` . $first_id . `) REFERENCES ` . $named_me . `(id)`,
                `ALTER TABLE ` . $table_name . ` ADD FOREIGN KEY (` . $second_id . `) REFERENCES ` . $named_object . `(id)`,
            ],
        ];
    }

    public function getGoodTypedValue($value): mixed
    {
        return (string) $value;
    }
}
