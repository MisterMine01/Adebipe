<?php

namespace App\Components;

use Adebipe\Router\Annotations\Route;
use Adebipe\Router\Response;
use Adebipe\Services\ORM;
use Adebipe\Components\Interfaces\ComponentInterface;
use App\Model\User;

class ORMComponents implements ComponentInterface
{
    #[Route(path: '/orm/update', method: 'GET', env: 'dev')]
    public static function update(ORM $orm): Response
    {
        $orm->update();
        return new Response('ORM updated');
    }
}
