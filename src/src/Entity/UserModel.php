<?php

namespace App\Model;

use Api\Model\Model;
use Api\Model\Type\DateTimeType;
use Api\Model\Type\IntType;
use Api\Model\Type\StringType;

class User extends Model
{
    public static $schema;
}

User::$schema = [
    'id' => new IntType(true, true),
    'username' => new StringType(true),
    'password' => new StringType(true),
    'email' => new StringType(true),
    'created_at' => new DateTimeType(true),
    'updated_at' => new DateTimeType(true),
];