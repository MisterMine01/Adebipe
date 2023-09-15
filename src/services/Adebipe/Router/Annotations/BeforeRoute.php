<?php

namespace Adebipe\Router\Annotations;

use Attribute;

#[Attribute]
interface BeforeRoute
{
    public function execute(): void;
}