<?php

namespace Adebipe\Router\Annotations;

use Attribute;

/**
 * Annotation for routes
 */
#[Attribute]
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
    ) {
    }
}
