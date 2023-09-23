<?php

namespace Adebipe\Services;

use Adebipe\Router\Response;
use Adebipe\Services\Interfaces\RegisterServiceInterface;

class Renderer implements RegisterServiceInterface
{
    private string $dir;

    public function __construct()
    {
        $this->dir = getenv('VIEW_DIR');
    }
    public function render(string $path, array $environements): Response
    {
        $result = 200;
        try {
            $body = $this->getTemplate($path, $environements);
        } catch (\Throwable $th) {
            $result = 500;
            $body = $th->getMessage();
        }
        return new Response($body, $result);
    }

    /**
     * Execute php file on path with environements variables and return the result
     */
    private function getTemplate(string $path, array $environements): string
    {
        extract($environements);
        ob_start();
        include $this->dir . $path;
        return ob_get_clean();
    }
}
