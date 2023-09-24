<?php

namespace Adebipe\Router\Annotations;

use Adebipe\Builder\NoBuildable;
use Attribute;

/**
 * Annotation for routes
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[Attribute]
#[NoBuildable]
class Route
{
    /**
     * Route constructor.
     *
     * @param string $path   Path of the route
     * @param string $method Method of the route
     * @param array  $regex  Regex of the route
     * @param string $env    Environment of the route (dev or prod)
     */
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
        public string $env = 'prod',
    ) {
    }
}
