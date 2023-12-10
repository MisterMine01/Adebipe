<?php

namespace Adebipe\Annotations;

use Adebipe\Router\Annotations\BeforeRoute;
use Adebipe\Router\Request;
use Adebipe\Router\Response;
use Adebipe\Services\Settings;
use Attribute;

/**
 * Validates the request body against a schema.
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[Attribute]
class ValidatePost extends BeforeRoute
{
    /**
     * Validates the request body against a schema.
     *
     * @param array $schema The schema to validate against.
     */
    public function __construct(
        public array $schema,
    ) {
    }

    /**
     * Validates the request body against a schema.
     *
     * @param Request|null $request The request to validate.
     *
     * @return bool|Response True if the request is valid, a response otherwise.
     */
    public function execute(?Request $request = null): mixed
    {
        $result = $request->validatePost($this->schema);
        if ($result !== true) {
            $message = Settings::getConfig('CORE.ERROR.HTTP_BAD_REQUEST.MESSAGE');
            $code = Settings::getConfig('CORE.ERROR.HTTP_BAD_REQUEST.CODE');
            $header = Settings::getConfig('CORE.ERROR.HTTP_BAD_REQUEST.BAD_REQUEST_HEADER');
            if (!$message) {
                $message = "Bad request";
            }
            if (!$code) {
                $code = 400;
            } else {
                $code = intval($code);
            }
            if (!$header) {
                $header = [];
            } else {
            }
            return new Response($message, $code, $header);
        }
        return true;
    }
}
