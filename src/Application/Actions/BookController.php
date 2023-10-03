<?php

namespace App\Application\Actions;

use App\Infrastructure\Persistence\Line\DbLineRepository;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;

class BookController extends Action
{
    public function __construct(
        LoggerInterface $logger,
        protected DbLineRepository $lineRepository,
        protected ContainerInterface $container
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $this->container->get('view')->render($this->response, 'book.html.twig');
        return $this->response;
    }

    public function lines(): Response
    {
        $lines = $this->lineRepository->findAll();

        return $this->respondWithData([
          'draw' => 1,
          'recordsTotal' => count($lines),
          'recordsFiltered' => count($lines),
          'data' => $lines,
        ]);
    }
}
