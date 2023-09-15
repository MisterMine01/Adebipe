<?php

namespace Adebipe\Router;

/**
 * Response class
 */
class JsonResponse extends Response
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
        array $body,
        /**
         * Status of the response
         * @var int
         */
        int $status = 200,
        /**
         * Headers of the response
         * @var array<string, string>
         */
        array $headers = []
    ) {
        $headers['Content-Type'] = 'application/json';
        parent::__construct(json_encode($body), $status, $headers);
    }
}
