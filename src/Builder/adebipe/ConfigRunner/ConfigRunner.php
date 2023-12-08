<?php

namespace Adebipe\Services\Generated;

use Adebipe\Services\Interfaces\CreatorInterface;
use Adebipe\Services\Settings;

/**
 * Initialize the environment variables
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ConfigRunner implements CreatorInterface
{
    private array $_env = ["HERE GOES ENV"];
    private array $_config = ["HERE GOES CONFIG"];

    /**
     * Load the environment variables
     */
    public function __construct()
    {
        foreach ($this->_env as $key => $value) {
            Settings::addEnvVariable($key, $value);
        }
        Settings::addConfigArray($this->_config, false);
    }
}
