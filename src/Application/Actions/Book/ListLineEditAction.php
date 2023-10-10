<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

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

            return $this->redirect($url);
        }
        if ($this->request->getParsedBody()) {
            $line->setType($this->request->getParsedBody()['type']);
            $line->setLabel($this->request->getParsedBody()['label']);
            $line->setName($this->request->getParsedBody()['name']);
            $line->setBreakdown($this->request->getParsedBody()['breakdown']);
            foreach (array_keys(LineBreakdown::getBreakdowns()) as $breakdown) {
                $key = 'breakdown' . $breakdown;
                $line->__set($key, self::parseCurrency($this->request->getParsedBody()[$key]));
            }
            $this->lineRepository->save($line);
            $routeParser = RouteContext::fromRequest($this->request)->getRouteParser();
            $url = $routeParser->urlFor('book');

            return $this->redirect($url);
        }
        $this->container->get('view')->render($this->response, 'edit_line.html.twig', [
          'line' => $line,
          'breakdowns' => LineBreakdown::getBreakdowns(),
        ]);
        return $this->response;
    }

    protected static function parseCurrency(?string $currency): float
    {
        if (empty($currency)) {
            return 0;
        }

        return (float)str_replace(',', '.', preg_replace('/[^-0-9,]/', '', $currency));
    }
}
