<?php

namespace Adebipe\Services\Interfaces;

use NoBuildable;

/**
 * Interface for services who need to set is own build
 * 
 * @package Adebipe\Services\Interfaces
 */
#[NoBuildable]
interface BuilderServiceInterface
{
    /**
     * Get the service builder name
     * 
     * @return string The code to add to the build
     */
    public function build(): string;
}
