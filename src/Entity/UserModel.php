<?php

namespace App\Model;

use Adebipe\Model\Model;
use Adebipe\Model\Type\DateTimeType;
use Adebipe\Model\Type\IntType;
use Adebipe\Model\Type\ManyToMany;
use Adebipe\Model\Type\StringType;

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