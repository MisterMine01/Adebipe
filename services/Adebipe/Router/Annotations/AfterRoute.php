<?php

namespace Adebipe\Router\Annotations;

abstract class AfterRoute
{
    public abstract function execute(): void;
}