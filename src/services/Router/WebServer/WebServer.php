<?php

namespace Api\Services;

use Api\Services\Interfaces\BuilderServiceInterface;

class WebServer implements BuilderServiceInterface
{
    /**
     * not build the service
     */
    public function build(string $classCode): ?string
    {
        return null;
    }

    public function start(DevRouter $router): void
    {
        // Get http connection
        $sock = socket_create_listen(getenv('PORT'));
        
    }


}