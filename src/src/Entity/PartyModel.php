<?php

namespace App\Model;

use Adebipe\Model\Model;
use Adebipe\Model\Type\DateTimeType;
use Adebipe\Model\Type\IntType;
use Adebipe\Model\Type\ManyToMany;
use Adebipe\Model\Type\StringType;

class Party extends Model
{
    public static function createSchema(): array
    {
        return [
            'id' => new IntType(true, true),
            'name' => new StringType(25, true),
            'description' => new StringType(200, true),
            'created_at' => new DateTimeType(true),
            'updated_at' => new DateTimeType(true),
            'users' => new ManyToMany(Party::class, User::class, false),
        ];
    }
}