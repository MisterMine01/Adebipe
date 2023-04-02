<?php

namespace Api\Services;

use Api\Services\Interfaces\StarterServiceInterface;

/**
 * Initialize the environment variables
 */
class Dotenv implements StarterServiceInterface
{
    private $variable = array();


    /**
     * Initialize the environment variables
     */
    public function atStart(Logger $logger = null): void
    {
        $logger->info('Initialize the environment variables');
    }

    /**
     * Nothing to do
     */
    public function atEnd(): void
    {
    }

    /**
     * Load the environment variables
     */
    public function __construct()
    {
        if (is_file('.env')) {
            $this->getEnvFile('.env');
        }
        $env = getenv('ENV');
        if ($env === false) {
            $env = 'dev';
        }
        if (is_file('.env.' . $env)) {
            $this->getEnvFile('.env.' . $env);
        }
        foreach ($this->variable as $key => $value) {
            putenv($key . '=' . $value);
        }
    }

    /**
     * Get the environment variables from a file
     * 
     * @param string $name The name of the file
     */
    private function getEnvFile($name)
    {
        $file = fopen($name, 'r');
        while (!feof($file)) {
            $line = fgets($file);
            $line = trim($line);
            if (strpos($line, '=') !== false) {
                $line = explode('=', $line);
                $this->variable[$line[0]] = $line[1];
            }
        }
        if (is_file($name . '.local')) {
            $this->getEnvFile($name . '.local');
        }
    }
}
