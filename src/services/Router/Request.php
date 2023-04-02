<?php

namespace Api\Router;

class Request
{

    // Method, url, headers, body, params, files, cookies, post, port, ip
    /**
     * Create a new request
     * 
     * @param string $method Method of the request
     * @param string $uri URI of the request
     * @param array<string, string> $headers Headers of the request
     * @param string $body Body of the request
     * @param array<string, string> $params Params of the request
     * @param array<string, string> $files Files of the request
     * @param array<string, string> $cookies Cookies of the request
     * @param array<string, string> $post POST of the request
     * @param int $port Port of the request
     * @param string $ip IP of the request
     */
    public function __construct(

        /**
         * Method of the request
         * @var string
         */
        public string $method,

        /**
         * URI of the request
         * @var string
         */
        public string $uri,

        /**
         * Headers of the request
         * @var array<string, string>
         */
        public array $headers,

        /**
         * Body of the request
         * @var string
         */
        public string $body,

        /**
         * Params of the request
         * @var array<string, string>
         */
        public array $params,

        /**
         * Files of the request
         * @var array<string, string>
         */
        public array $files,

        /**
         * Cookies of the request
         * @var array<string, string>
         */
        public array $cookies,

        /**
         * POST of the request
         * @var array<string, string>
         */
        public array $post,

        /**
         * Port of the request
         * @var int
         */
        public int $port,

        /**
         * IP of the request
         * @var string
         */
        public string $ip
    ) {
    }
}
