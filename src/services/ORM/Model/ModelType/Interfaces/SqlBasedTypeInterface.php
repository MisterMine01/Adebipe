<?php

namespace Adebipe\Model\Type;

use Adebipe\Services\MsQl;

interface SqlBasedTypeInterface
{
    public function getResultFromDb(MsQl $msql, string $id);

    public function addToDb(MsQl $msql, string $id, object $value): bool;

    public function deleteToDb(MsQl $msql, string $id, object $value): bool;
}