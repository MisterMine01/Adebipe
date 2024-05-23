<?php

namespace Adebipe\Services;

use Adebipe\Router\Response;
use Adebipe\Services\Interfaces\RegisterServiceInterface;

/**
 * Render a view
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class Renderer implements RegisterServiceInterface
{
    private string $_dir;

    /**
     * Renderer constructor.
     */
    public function __construct()
    {
        $this->_dir = Settings::getConfig('DIR') . Settings::getConfig('CORE.RENDERER.VIEW_DIR');
    }

    /**
     * Render a view with environements variables
     *
     * @param string $path          The path of the view
     * @param array  $environements The environements variables
     *
     * @return Response
     */
    public function render(string $path, array $environements): Response
    {
        $result = 200;
        try {
            $body = $this->_getTemplate($path, $environements);
        } catch (\Throwable $th) {
            $result = 500;
            $body = $th->getMessage();
        }
        return new Response($body, $result);
    }

    /**
     * Execute php file on path with environements variables and return the result
     *
     * @param string $path          The path of the file
     * @param array  $environements The environements variables
     *
     * @return string
     */
    private function _getTemplate(string $path, array $environements): string
    {
        extract($environements);
        if (file_exists($this->_dir . $path)) {
            ob_start();
            include $this->_dir . $path;
        } else {
            throw new \Exception("Internal Server Error");
        }
        return ob_get_clean() ?: '';
    }
}
