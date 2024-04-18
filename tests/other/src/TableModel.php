<?php

namespace App\Model;

use Adebipe\Model\ORMTableCreator;

class TableModel extends ORMTableCreator
{
    public function __construct()
    {
    }

    public function getFixtures(): array
    {
        return [];
    }
}
