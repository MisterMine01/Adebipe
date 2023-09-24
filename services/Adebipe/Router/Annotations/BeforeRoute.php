<?php

namespace Adebipe\Router\Annotations;

/**
 * BeforeRoute annotation
 * This annotation is executed before the route
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
abstract class BeforeRoute
{
    /**
     * BeforeRoute annotation executor
     * return true if the request is valid
     * return false if the request is invalid and you don't want to return a response
     *      (Response will be returned automatically)
     * return Response if the request is invalid and you want to return a response
     *
     * All other return types throw an exception
     *
     * @return mixed
     */
    abstract public function execute(): mixed;
}
