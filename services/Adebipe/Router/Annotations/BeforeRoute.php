<?php

namespace Adebipe\Router\Annotations;

use Adebipe\Router\Response;
use Attribute;
use ReflectionMethod;

abstract class BeforeRoute
{
    /**
     * BeforeRoute annotation executor
     * return true if the request is valid
     * return false if the request is invalid and you don't want to return a response (Response will be returned automatically)
     * return Response if the request is invalid and you want to return a response
     * 
     * All other return types throw an exception
     * 
     * @return mixed
     */
    public function __construct(
        private $function = new ReflectionMethod(self::class, 'execute')
    )
    {
        
    }

    public abstract function execute(): mixed;
}