<?php

namespace Adebipe\Model\Type;

use Adebipe\Services\MsQl;

interface SqlBasedTypeInterface
{
    public function getResultFromDb(MsQl $msql, string $id);
}