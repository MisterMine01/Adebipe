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

    #[Route(path: '/orm/data', method: 'GET')]
    public static function test(ORM $orm): Response
    {
        $repositories = $orm->getRepositories();
        foreach ($repositories as $repository) {
            echo $repository->getTableName() . "<br>";
            $all = $repository->findAll();
            foreach ($all as $one) {
                echo $one->username . "<br>";
                var_dump(count($one->parties));
                $one->first;
            }
        }
        return new Response('');
    }

}