<?php

namespace App\Repository;

use App\Entity\Conference;
use App\Entity\Report;
use App\Form\ReportType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Report>
 *
 * @method Report|null find($id, $lockMode = null, $lockVersion = null)
 * @method Report|null findOneBy(array $criteria, array $orderBy = null)
 * @method Report[]    findAll()
 * @method Report[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct($registry, Report::class);
    }

    public function saveData(object $entity): void
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function getAvailableTimeForReport(int $conferenceId): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->select('r.startedAt')
            ->join('r.conference', 'c')
            ->where("c = $conferenceId");
    }

    public function findOverlappingReport(
        \DateTime $startTime,
        \DateTime $endTime,
        int $conferenceId
    ): ?\DateTimeInterface
    {
        $existingReports = $this->createQueryBuilder('r')
            ->join('r.conference', 'c')
            ->where("c = $conferenceId")
            ->where('r.startedAt < :endTime')
            ->andWhere('r.endedAt > :startTime')
            ->setParameters([
                'startTime' => $startTime,
                'endTime' => $endTime,
            ])
            ->getQuery()
            ->getResult();

        if (count($existingReports) > 0) {
            // Знайдемо найближчий можливий час початку
            $closestStartTime = null;
            foreach ($existingReports as $report) {
                if (
                    $report->getEndedAt() > $startTime
                    &&
                    ($closestStartTime === null || $report->getEndedAt() < $closestStartTime)
                )
                {
                    $closestStartTime = $report->getEndedAt();
                }
            }

            return $closestStartTime ?? null;
        }

        return null;
    }

    public function deleteReport(Report $report): void
    {
        $this->_em->remove($report);
        $this->_em->flush();
    }
}
