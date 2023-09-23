<?php

namespace Adebipe\Services\Interfaces;

/**
 * Interface for services who need to set functions at the start and end of the application
 *
 * @package Adebipe\Services\Interfaces
 */
interface StarterServiceInterface extends CreatorInterface
{
    /**
     * Function to run at the start of the application
     */
    public function atStart(): void;

    /**
     * Function to run at the end of the application
     */
    public function atEnd(): void;
}
