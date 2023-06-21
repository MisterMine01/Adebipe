<?php


namespace App\Components;

use Api\Router\Annotations\Route;
use Api\Router\Response;
use Api\Services\ORM;
use App\Components\Interfaces\ComponentInterface;

class ORMComponents implements ComponentInterface
{
    #[Route(path: '/orm/update', method: 'GET')]
    public static function update(ORM $orm): Response
    {
        $orm->update();
        return new Response('ORM updated');
    }

}