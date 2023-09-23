<?php

namespace Adebipe\Router\Annotations;

abstract class AfterRoute
{
    abstract public function execute(): void;
}
