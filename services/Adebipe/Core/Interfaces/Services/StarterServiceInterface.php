<?php

namespace Adebipe\Services\Interfaces;

/**
 * Interface for services who need to set functions
 * at the start and end of the application
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface StarterServiceInterface extends CreatorInterface
{
    /**
     * Function to run at the start of the application
     *
     * @return void
     */
    public function atStart(): void;

    /**
     * Function to run at the end of the application
     *
     * @return void
     */
    public function atEnd(): void;
}
