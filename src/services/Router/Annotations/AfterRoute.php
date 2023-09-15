<?php

namespace Adebipe\Router\Annotations;

use Attribute;

#[Attribute]
interface AfterRoute
{
    public function execute(): void;
}