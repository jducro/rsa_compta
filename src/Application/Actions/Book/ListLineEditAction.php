<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Slim\Routing\RouteContext;

use Psr\Http\Message\ResponseInterface as Response;

class ListLineEditAction extends Action
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
      $line = $this->lineRepository->findLineOfId($this->args['id']);
      if (!$line) {
        $routeParser = RouteContext::fromRequest($this->request)->getRouteParser();
        $url = $routeParser->urlFor('book');

        return $this->response
          ->withHeader('Location', $url)
          ->withStatus(302);
      }
      $this->container->get('view')->render($this->response, 'edit_line.html.twig', [
        'line' => $line,
        'breakdowns' => LineBreakdown::getBreakdowns(),
      ]);
      return $this->response;
    }
}
