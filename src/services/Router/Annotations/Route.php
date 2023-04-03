<?php

namespace Api\Router\Annotations;

use Attribute;

/**
 * Annotation for routes
 */
#[Attribute]
class Route {
    public function __construct(
        /**
         * Path of the route
         */
        public string $path,
        public string $method
    ) {}
}
