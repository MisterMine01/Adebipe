<?php

namespace Adebipe\Router;

/**
 * Request class
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Request
{
    // Method, url, headers, body, params, files, cookies, post, port, ip
    /**
     * Create a new request
     *
     * @param string                $method  Method of the request
     * @param string                $uri     URI of the request
     * @param array<string, string> $headers Headers of the request
     * @param string                $body    Body of the request
     * @param array<string, string> $get     Params of the request
     * @param array<string, string> $files   Files of the request
     * @param array<string, string> $cookies Cookies of the request
     * @param array<string, string> $post    POST of the request
     * @param int                   $port    Port of the request
     * @param string                $ip      IP of the request
     */
    public function __construct(
        /**
         * Method of the request
         *
         * @var string
         */
        public string $method,
        /**
         * URI of the request
         *
         * @var string
         */
        public string $uri,
        /**
         * Headers of the request
         *
         * @var array<string, string>
         */
        public array $headers,
        /**
         * Body of the request
         *
         * @var string
         */
        public string $body,
        /**
         * Params of the request
         *
         * @var array<string, string>
         */
        public array $get,
        /**
         * Files of the request
         *
         * @var array<string, string>
         */
        public array $files,
        /**
         * Cookies of the request
         *
         * @var array<string, string>
         */
        public array $cookies,
        /**
         * POST of the request
         *
         * @var array<string, string>
         */
        public array $post,
        /**
         * Port of the request
         *
         * @var int
         */
        public int $port,
        /**
         * IP of the request
         *
         * @var string
         */
        public string $ip
    ) {
        if (self::_isJson($this->body)) {
            $data = json_decode($this->body, true);
            $this->post = array_merge($this->post, $data);
        }
    }

    /**
     * Get an integer param from the GET
     *
     * @param string $key     The key of the param
     * @param int    $default The default value
     *
     * @return int
     */
    public function getIntGet(string $key, int $default = 0): int
    {
        if (!isset($this->get[$key])) {
            return $default;
        }
        return intval($this->get[$key] ?? $default);
    }

    /**
     * Test if the post is valid with a schema
     *
     * @param array<string, string> $schema The schema
     *
     * @todo Add support for sub schema
     *
     * @return bool
     */
    public function validatePost(array $schema): bool
    {
        foreach ($schema as $key => $type) {
            if (strpos($type, '?') !== false) {
                continue;
            }
            if (!isset($this->post[$key])) {
                return false;
            }
            if (get_debug_type($this->post[$key]) !== $type) {
                return false;
            }
        }
        return true;
    }

    /**
     * Test if a string is a valid json
     *
     * @param string $string The string to test
     *
     * @return bool
     */
    private static function _isJson($string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
