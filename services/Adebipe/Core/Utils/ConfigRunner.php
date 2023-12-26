<?php

namespace Adebipe\Services;

use Adebipe\Services\Interfaces\BuilderServiceInterface;
use Adebipe\Services\Interfaces\StarterServiceInterface;

/**
 * Initialize the environment variables
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ConfigRunner implements StarterServiceInterface, BuilderServiceInterface
{
    private array $_variable = array();

    /**
     * Load the environment variables
     */
    public function __construct()
    {
        $env = getenv('ENV');
        if ($env === false) {
            throw new \Exception("The environment variable ENV is not set");
        }
        $files = [
            ".env",
            ".env." . $env,
            ".env.local",
            ".env." . $env . ".local"
        ];
        foreach ($files as $file) {
            if (is_file($file)) {
                $this->_readEnvFile($file);
            }
        }
        // If the config file is set
        if ($config_name = $this->_variable['CONFIG'] ?? null) {
            $config_dir = $this->_variable['CONFIG_DIR'] ?? null;
            if ($config_dir === null) {
                $config_dir = 'config/';
            }
            $config_path = $config_dir . '/' . $config_name . '.php';
            $this->_readConfigFile($config_path);
        }
        foreach ($this->_variable as $key => $value) {
            Settings::addEnvVariable($key, $value);
        }
    }

    /**
     * Get the service builder name
     *
     * @return string path to the builder of the service
     */
    public function build(): string
    {
        return "adebipe/ConfigRunner/RunnerBuilder.php";
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
        if (!$logger) {
            throw new \Exception("Logger service not found");
        }
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
     * Get the config
     *
     * @param string $config_path The path of the config
     *
     * @return void
     */
    private function _readConfigFile(string $config_path): void
    {
        if (is_file($config_path)) {
            $config = include_once $config_path;
            Settings::addConfigArray($config['config'] ?? [], false);
            foreach ($config['env_var'] ?? [] as $key => $value) {
                $this->_variable[$key] = $value;
            }
        }
    }

    /**
     * Get the environment variables from a file
     *
     * @param string $name The name of the file
     *
     * @return void
     */
    private function _readEnvFile(string $name): void
    {
        $file = fopen($name, 'r');
        // @infection-ignore-all
        if ($file === false) {
            return;
        }
        while (!feof($file)) {
            $line = fgets($file);
            // @infection-ignore-all
            if ($line === false) {
                continue;
            }
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            if (str_starts_with($line, "#")) {
                continue;
            }
            if (strpos($line, '=') === false) {
                throw new \Exception("Invalid line in the file " . $name . ": " . $line);
            }
            $line = explode('=', $line);
            $this->_variable[$line[0]] = $line[1];
        }
    }
}
