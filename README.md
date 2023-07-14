# Adebipe

Api for
Development
Environment with
Builder for
Improved
Production
Environment

## Start development environment with docker

Adebipe uses docker-compose to start a development environment.

```bash
docker-compose up -d
```

After that you can access to your application at http://localhost:8000.

You also have access to phpmyadmin at http://localhost:8080.

## Stop development environment with docker

```bash
docker-compose down
```

## How to use

### Create a new component

go to `src/Component` and create a new file with the following content:

```php
<?php

namespace App\Components;

use Adebipe\Components\Interfaces\ComponentInterface;
use Adebipe\Router\Annotations\Route;
use Adebipe\Router\Response;

class ExempleComponents implements ComponentInterface
{
    #[Route(path: '/exemple', method: 'GET')]
    public function exemple(): Response
    {
        return new Response('Hello World');
    }
}
```

## Difference between production and development routing

In development mode, the router will use the annotations to generate the routes.
This process is slow but it's very useful for development because you don't need to restart the server to see your changes.

In production mode, the router will generate the routes at the build time.
The build time is slow but the router will be faster in production.

### Amelioration for production

For production, the idea is to not use the injector.
In development, the injector is use everywhere and everytime.
But in production, the injector is only use for generate parameters for the routes.
It can be a good idea to generate functions at build time to avoid the injector.

## How to build

```bash
docker-compose exec php php build
```