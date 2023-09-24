<?php

namespace Adebipe\Router\Annotations;

/**
 * AfterRoute annotation
 * This annotation is executed after the route
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
abstract class AfterRoute
{
    /**
     * AfterRoute annotation executor
     *
     * @todo Not implemented yet
     * The after route annotation is not executed yet in the router
     *
     * @return void
     */
    abstract public function execute(): void;
}
