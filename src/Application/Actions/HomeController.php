<?php

namespace App\Application\Actions;

use Psr\Container\ContainerInterface;

class HomeController
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function home($request, $response, $args)
    {
        $this->container->get('view')->render($response, 'home.html.twig', [
          'name' => 'John Doe'
        ]);
        return $response;
    }
}
