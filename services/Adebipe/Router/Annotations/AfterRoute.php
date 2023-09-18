<?php

namespace Adebipe\Router\Annotations;

use Attribute;
use NoBuildable;

#[Attribute]
#[NoBuildable]
abstract class AfterRoute
{
    public abstract function execute(): void;
}