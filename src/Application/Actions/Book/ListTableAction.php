<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListTableAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        protected ContainerInterface $container
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $this->container->get('view')->render($this->response, 'book.html.twig');
        return $this->response;
    }
}
