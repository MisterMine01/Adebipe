<?php

namespace Api\Router;

class Response {
    public function __construct(
        public string $body,
        public int $status = 200,
        public array $headers = []
    ) {}
}