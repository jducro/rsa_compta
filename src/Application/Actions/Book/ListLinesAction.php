<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Domain\Line;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use Psr\Log\LoggerInterface;

use Psr\Http\Message\ResponseInterface as Response;

class ListLinesAction extends Action
{

    public function __construct(
      LoggerInterface $logger, 
      protected DbLineRepository $lineRepository, 
    ) {
      parent::__construct($logger);
    }

    protected function action(): Response
    {
      $params = $this->request->getQueryParams();

      $qb = $this->lineRepository->getQueryBuilder();
      if (isset($params['search']['value']) && !empty($params['search']['value'])) {
        $qb->orWhere('l.amount LIKE :search');
        $qb->orWhere('l.date LIKE :search');
        $qb->orWhere('l.type LIKE :search');
        $qb->orWhere('l.label LIKE :search');
        $qb->orWhere('l.name LIKE :search');
        $qb->setParameter('search', '%'.$params['search']['value'].'%');
      }
      // $qb->andWhere('l.breakdown NOT IN (:breakdown)');
      // $qb->setParameter('breakdown', [LineBreakdown::PaypalFees, LineBreakdown::SogecomFees, LineBreakdown::InternalTransfer]);
      $qbLines = $qb;
      if (!empty($params['order'])) {
        $sort = $params['columns'][$params['order'][0]['column']]['data'];
        $order = $params['order'][0]['dir'];
        $lines = $qbLines->orderBy('l.'.$sort, $order);
      }
      $lines = $qbLines->getQuery()
        ->setFirstResult(!empty($params['start']) ? $params['start'] : 0)
        ->setMaxResults(!empty($params['length']) ? $params['length'] : 10)
        ->getResult();

      return $this->respondWithData([
        'draw' => !empty($params['draw']) ? $params['draw'] : 1,
        'recordsTotal' => $this->lineRepository->countAll(),
        'recordsFiltered' => $qb->select('count(l.id)')->getQuery()->getSingleScalarResult(),
        'data' => $lines,
      ]);
    }
}
