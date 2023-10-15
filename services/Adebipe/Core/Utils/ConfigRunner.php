<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\StarterServiceInterface;

/**
 * Initialize the environment variables
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ConfigRunner implements StarterServiceInterface
{
    private $_variable = array();

    /**
     * Load the environment variables
     */
    public function __construct()
    {
        if (is_file('.env')) {
            $this->_getEnvFile('.env');
        }
        $env = getenv('ENV');
        if ($env === false) {
            $env = 'dev';
        }
        if (is_file('.env.' . $env)) {
            $this->_getEnvFile('.env.' . $env);
        }
        foreach ($this->_variable as $key => $value) {
            putenv($key . '=' . $value);
        }
    }


    /**
     * Function to run at the start of the application
     *
     * @param Logger $logger The logger to use
     *
     * @return void
     */
    public function atStart(Logger $logger = null): void
    {
        $logger->info('Initialize the environment variables');
    }

    /**
     * Function to run at the end of the application
     *
     * @return void
     */
    public function atEnd(): void
    {
    }

    /**
     * Get the environment variables from a file
     *
     * @param string $name The name of the file
     *
     * @return void
     */
    private function _getEnvFile(string $name): void
    {
        $file = fopen($name, 'r');
        while (!feof($file)) {
            $line = fgets($file);
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            if (strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                $line = explode('=', $line);
                $this->_variable[$line[0]] = $line[1];
            }
        }
        if (is_file($name . '.local')) {
            $this->_getEnvFile($name . '.local');
        }
    }
}
