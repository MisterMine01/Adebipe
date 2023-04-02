<?php

namespace Api\Router\Annotations;

use Attribute;

#[Attribute]
class Route {
    public function __construct(
        public string $path,
        public string $method
    ) {}
}
