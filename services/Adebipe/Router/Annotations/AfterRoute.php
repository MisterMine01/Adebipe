<?php

namespace Adebipe\Router\Annotations;

use Attribute;
use NoBuildableAttribute;

#[Attribute]
abstract class AfterRoute
{
    public abstract function execute(): void;
}