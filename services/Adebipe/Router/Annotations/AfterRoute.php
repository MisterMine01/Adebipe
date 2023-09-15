<?php

namespace Adebipe\Router\Annotations;

use Attribute;

#[Attribute]
abstract class AfterRoute
{
    public abstract function execute(): void;
}