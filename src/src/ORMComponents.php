<?php


namespace App\Components;

use Adebipe\Router\Annotations\Route;
use Adebipe\Router\Response;
use Adebipe\Services\ORM;
use App\Components\Interfaces\ComponentInterface;
use App\Model\User;

class ORMComponents implements ComponentInterface
{
    #[Route(path: '/orm/update', method: 'GET')]
    public static function update(ORM $orm): Response
    {
        $orm->update();
        return new Response('ORM updated');
    }

    #[Route(path: '/orm/data', method: 'GET')]
    public static function test(ORM $orm): Response
    {
        $repository = $orm->getRepository(User::class);
        echo $repository->getTableName() . "<br>";
        $all = $repository->findAll();
        foreach ($all as $one) {
            echo $one->username . "<br>";
            var_dump(count($one->parties));
        }
        return new Response('');
    }

}