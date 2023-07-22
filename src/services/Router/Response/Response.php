<?php

namespace Adebipe\Router;

/**
 * Response class
 */
class Response
{
    /**
     * Response constructor
     * @param string $body Body of the response
     * @param int $status Status of the response
     * @param array<string, string> $headers Headers of the response
     */
    public function __construct(
        /**
         * Body of the response
         * @var string
         */
        public string $body,
        /**
         * Status of the response
         * @var int
         */
        public int $status = 200,
        /**
         * Headers of the response
         * @var array<string, string>
         */
        public array $headers = []
    ) {
    }

    /**
     * Send the response
     * @return void
     */
    public function send()
    {
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->body;
        http_response_code($this->status);
    }
}
