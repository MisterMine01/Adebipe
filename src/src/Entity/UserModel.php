<?php

namespace App\Model;

use Api\Model\Model;
use Api\Model\Type\DateTimeType;
use Api\Model\Type\IntType;
use Api\Model\Type\ManyToMany;
use Api\Model\Type\StringType;

class User extends Model
{
    public static function createSchema(): array
    {
        return [
            'id' => new IntType(true, true),
            'username' => new StringType(25, true),
            'password' => new StringType(50, true),
            'email' => new StringType(100, true),
            'created_at' => new DateTimeType(true),
            'updated_at' => new DateTimeType(true),
            'parties' => new ManyToMany(User::class, Party::class, true),
        ];
    }
}