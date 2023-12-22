<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Domain\Line;
use App\Domain\LineBreakdown;
use App\Domain\CheckDeliveryLine;
use App\Infrastructure\Persistence\CheckDelivery\DbCheckDeliveryRepository;
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
        protected DbCheckDeliveryRepository $checkDeliveryRepository,
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
        if (!empty($this->request->getParsedBody()['check_delivery'])) {
            return $this->convertCheckDelivery($line);
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
        $vars = [
            'line' => $line,
            'breakdowns' => LineBreakdown::getBreakdowns(),
        ];
        if ($line->getLabel() === "REMISES DE CHEQUES") {
            $checkCount = 0;
            if (\preg_match('/DE\s+([0-9]+)\s+CHQ/', $line->getDescription(), $matches)) {
                $checkCount = $matches[1];
            }
            $vars['check_count'] = $checkCount;
            $vars['check_deliveries'] = $this->checkDeliveryRepository
                ->findByDifference($line->getAmount(), $checkCount, $line->getDate());
        }
        $this->container->get('view')->render($this->response, 'edit_line.html.twig', $vars);
        return $this->response;
    }

    protected function convertCheckDelivery($line): Response
    {
        $checkDelivery = $this->checkDeliveryRepository
            ->findCheckDeliveryOfId($this->request->getParsedBody()['check_delivery']);

        foreach ($checkDelivery->getLines() as $checkDeliveryLine) {
            $line = new Line();
            $line->setType('CHQ');
            $line->setName($checkDeliveryLine->getName());
            $line->setLabel($checkDeliveryLine->getLabel());
            $line->setDescription(
                "Chèque n°" . $checkDeliveryLine->getCheckNumber() . " remis le " . $checkDelivery
                    ->getDate()->format('d/m/Y')
            );
            $line->setAmount($checkDeliveryLine->getAmount());
            if (strpos($checkDeliveryLine->getLabel(), 'COTISATION') === 0) {
                $line->setBreakdown([LineBreakdown::]);
                $line->breakdownInternalTransfer = $line->getAmount();
            } else {
                $line->setBreakdown([LineBreakdown::CHECK]);
                $line->breakdownCheck = $line->getAmount();
            }
        }

        $routeParser = RouteContext::fromRequest($this->request)->getRouteParser();
        $url = $routeParser->urlFor('book');

        return $this->redirect($url);
    }

    protected static function parseCurrency(?string $currency): float
    {
        if (empty($currency)) {
            return 0;
        }

        return (float)str_replace(',', '.', preg_replace('/[^-0-9,]/', '', $currency));
    }
}
