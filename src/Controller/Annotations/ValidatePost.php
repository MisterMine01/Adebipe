<?php

namespace Adebipe\Annotations;

use Adebipe\Router\Annotations\BeforeRoute;
use Adebipe\Router\Request;
use Adebipe\Router\Response;
use Attribute;
use NoBuildable;

#[Attribute]
class ValidatePost extends BeforeRoute
{
    public function __construct(
        public array $schema,
    )
    {
        
    }

    public function execute(?Request $request = null): mixed
    {
        $result = $request->validatePost($this->schema);
        if ($result !== true) {
            $message = getenv('HTTP_BAD_REQUEST_MESSAGE');
            $code = getenv('HTTP_BAD_REQUEST_CODE');
            $header = getenv('HTTP_BAD_REQUEST_HEADER');
            if ($message === false)
                $message = "Bad request";
            if ($code === false)
                $code = 400;
            if ($header === false)
                $header = [];
            return new Response($message, $code, $header);
        }
        return true;
    }
}