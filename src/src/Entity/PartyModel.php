<?php

namespace App\Model;

use Api\Model\Model;
use Api\Model\Type\DateTimeType;
use Api\Model\Type\IntType;
use Api\Model\Type\StringType;

class Party extends Model
{
    public static $schema;
}

Party::$schema = [
    'id' => new IntType(true, true),
    'name' => new StringType(25, true),
    'description' => new StringType(200, true),
    'created_at' => new DateTimeType(true),
    'updated_at' => new DateTimeType(true),
];