<?php

namespace Api\Model\Type;

use Api\Services\MsQl;

interface SqlBasedTypeInterface
{
    public function getResultFromDb(MsQl $msql, string $id);
}