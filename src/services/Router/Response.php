<?php

namespace Api\Router;

class Response {
    public function __construct(
        public string $body,
        public int $status = 200,
        public array $headers = []
    ) {}

    public function send() {
        echo $this->body;
        http_response_code($this->status);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }
}