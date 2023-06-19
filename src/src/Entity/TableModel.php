<?php

namespace App\Model;

use Api\Model\ORMTableCreator;

class TableModel extends ORMTableCreator
{
    public function __construct()
    {
        $this->create_model(User::class);
    }
}
