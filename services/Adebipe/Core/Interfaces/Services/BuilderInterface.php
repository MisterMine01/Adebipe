<?php

namespace Adebipe\Services\Interfaces;

use Adebipe\Builder\NoBuildable;

/**
 * Interface for services who need to set is own build
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[NoBuildable]
interface BuilderServiceInterface
{
    /**
     * Get the service builder name
     *
     * @return string path to the builder of the service
     */
    public function build(): string;
}
