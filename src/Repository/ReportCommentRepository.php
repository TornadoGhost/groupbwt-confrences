<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report;
use App\Entity\ReportComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReportComment>
 *
 * @method ReportComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReportComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReportComment[]    findAll()
 * @method ReportComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportComment::class);
    }

    public function add(ReportComment $entity, bool $flush = false): ?ReportComment
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();

            return $entity;
        }

        return null;
    }

    public function remove(ReportComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllCommentsByReportId(int $reportId): ?array
    {
        return $this->createQueryBuilder('c')
            ->join('c.report', 'r')
            ->where('r.id = :reportId')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameters([
                'reportId' => $reportId
            ])
            ->getQuery()
            ->getResult();
    }

    public function getCommentsByReportQueryBuilder(Report $report): QueryBuilder
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u', Join::WITH)
            ->where('c.report = :report')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('report', $report)
            ->orderBy('c.createdAt', 'DESC');
    }
}
