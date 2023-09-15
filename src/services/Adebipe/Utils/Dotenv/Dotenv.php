<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\StarterServiceInterface;

/**
 * Initialize the environment variables
 * 
 * @package Adebipe\Services
 */
class Dotenv implements StarterServiceInterface
{
    /**
     * all environment variables from the .env file
     * @var array<string> $variable
     */
    private $variable = array();


    public function atStart(Logger $logger = null): void
    {
        $logger->info('Initialize the environment variables');
    }

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
    private function getEnvFile(string $name): void
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
                $this->variable[$line[0]] = $line[1];
            }
        }
        if (is_file($name . '.local')) {
            $this->getEnvFile($name . '.local');
        }
    }
}
