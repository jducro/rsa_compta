<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Domain\LineBreakdown;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use Doctrine\ORM\QueryBuilder;
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

        $qb = $this->getQueryBuilder();
        if (isset($params['search']['value']) && !empty($params['search']['value'])) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->andX('l.amount LIKE :search'),
                $qb->expr()->andX('l.date LIKE :search'),
                $qb->expr()->andX('l.type LIKE :search'),
                $qb->expr()->andX('l.label LIKE :search'),
                $qb->expr()->andX('l.name LIKE :search'),
            ));
            $qb->setParameter('search', '%' . $params['search']['value'] . '%');
        }

        $qbLines = $qb;
        if (!empty($params['order'])) {
            $sort = $params['columns'][$params['order'][0]['column']]['data'];
            $order = $params['order'][0]['dir'];
            $lines = $qbLines->orderBy('l.' . $sort, $order);
        }
        $lines = $qbLines->getQuery()
          ->setFirstResult(!empty($params['start']) ? $params['start'] : 0)
          ->setMaxResults(!empty($params['length']) ? $params['length'] : 10)
          ->getResult();

        return $this->respondWithData([
          'draw' => !empty($params['draw']) ? $params['draw'] : 1,
          'recordsTotal' => $this->getQueryBuilder()->select('count(l.id)')->getQuery()->getSingleScalarResult(),
          'recordsFiltered' => $qb->select('count(l.id)')->getQuery()->getSingleScalarResult(),
          'data' => $lines,
        ]);
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $breakdowns = [LineBreakdown::PAYPAL_FEES, LineBreakdown::SOGECOM_FEES, LineBreakdown::INTERNAL_TRANSFER];
        $qb = $this->lineRepository->getQueryBuilder();
        $qb->where('l.breakdown IS NULL OR l.breakdown NOT IN (:breakdown)');
        $qb->setParameter('breakdown', $breakdowns);
        return $qb;
    }
}
