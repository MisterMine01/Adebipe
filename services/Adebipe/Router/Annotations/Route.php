<?php

namespace Adebipe\Router\Annotations;

use Attribute;
use NoBuildable;

/**
 * Annotation for routes
 */
#[Attribute]
#[NoBuildable]
class Route
{
    public function __construct(
        /**
         * Path of the route
         */
        public string $path,
        /**
         * Method of the route
         */
        public string $method,
        public array $regex = [],
        public string $env = 'PROD',
    ) {
    }
}
