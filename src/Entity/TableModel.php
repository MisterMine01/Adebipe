<?php

namespace App\Model;

use Adebipe\Model\ORMTableCreator;

class TableModel extends ORMTableCreator
{
    public function __construct()
    {
        $this->createModel(User::class);
        $this->createModel(Party::class);
    }

    public function getFixtures(): array
    {
        return [];
    }
}
