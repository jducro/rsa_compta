<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use App\Services\ExcelExportService;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

use Psr\Http\Message\ResponseInterface as Response;

class ExcelAction extends Action
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
    $this->container->get(ExcelExportService::class)->export();
    return $this->response
          ->withHeader('Location', '/export.xlsx')
          ->withStatus(302);
  }
}