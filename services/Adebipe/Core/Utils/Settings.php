<?php

namespace Adebipe\Services;

/**
 * The static class to configure the application
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Settings
{
    private static array $_env_variable = array();
    private static array $_config = array();

    /**
     * Add an environment variable
     *
     * @param string $key   The key of the configuration
     * @param string $value The value of the variable
     *
     * @return void
     */
    public static function addEnvVariable(string $key, string $value): void
    {
        self::$_env_variable[$key] = $value;
        putenv($key . '=' . $value);
    }

    /**
     * Get an environment variable
     *
     * @param string $key The key of the configuration
     *
     * @return string|null
     */
    public static function getEnvVariable(string $key): ?string
    {
        if (!isset(self::$_env_variable[$key])) {
            $env = getenv($key);
            if (empty($env)) {
                return null;
            }
            self::$_env_variable[$key] = $env;
        }
        return self::$_env_variable[$key];
    }

    /**
     * Add a configuration from an array
     *
     * @param array $config The configuration to add
     * @param bool  $merge  If the configuration must be merged with the existing one
     *
     * @return void
     */
    public static function addConfigArray(array $config, $merge = true): void
    {
        if ($merge) {
            self::$_config = array_merge(self::$_config, $config);
        } else {
            self::$_config = $config;
        }
    }

    /**
     * Add a configuration with a key and a value
     *
     * @param string $key   The key of the configuration
     * @param mixed  $value The value of the configuration
     *
     * @return void
     */
    public static function addConfig(string $key, mixed $value): void
    {
        if ($key === null) {
            return;
        }
        $keys = explode('.', $key);
        $config = &self::$_config;
        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                $config[$key] = array();
            }
            $config = &$config[$key];
        }
        $config = $value;
        return;
    }

    /**
     * Get a configuration value
     *
     * @param string $key The key of the configuration to get (if in a sub-array, use a dot to separate the keys)
     *
     * @return mixed
     */
    public static function getConfig(?string $key): mixed
    {
        if ($key === null) {
            return self::$_config;
        }
        $keys = explode('.', $key);
        $config = self::$_config;
        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                return null;
            }
            $config = $config[$key];
        }
        return $config;
    }
}
