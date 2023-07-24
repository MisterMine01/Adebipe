<?php

namespace Adebipe\Services\Interfaces;

/**
 * Interface for services who need to set is own build
 * 
 * @package Adebipe\Services\Interfaces
 */
interface BuilderServiceInterface
{
    /**
     * Build the service
     * 
     * @return string The code to add to the build
     */
    public function build(string $classCode): ?string;

    /**
     * Append files to the build
     * @return array The files to append to the build
     */
    public function appendFiles(): array;
}
