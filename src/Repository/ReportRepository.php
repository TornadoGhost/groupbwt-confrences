<?php

declare(strict_types=1);

namespace App\Repository;

use App\DTO\Request\IndexReportRequest;
use App\Entity\Conference;
use App\Entity\Report;
use App\Entity\ReportComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findOverlappingReport(
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        int                $conferenceId,
        ?int               $reportId = null
    ): ?\DateTimeInterface
    {
        $existingReportsBuilder = $this->createQueryBuilder('r')
            ->join('r.conference', 'c')
            ->where('r.startedAt < :endTime')
            ->andWhere('r.endedAt > :startTime')
            ->andWhere('c.id = :conferenceId')
            ->andWhere('r.deletedAt IS NULL')
            ->setParameters([
                'startTime' => $startTime,
                'endTime' => $endTime,
                'conferenceId' => $conferenceId,
            ]);

        if ($reportId) {
            $existingReportsBuilder
                ->andWhere('r.id != :reportId')
                ->setParameter('reportId', $reportId);
        }

        $existingReportsResult = $existingReportsBuilder->getQuery()->getResult();

        if (count($existingReportsResult) > 0) {
            $closestStartTime = null;
            foreach ($existingReportsResult as $report) {
                if (
                    $report->getEndedAt() > $startTime
                    &&
                    ($closestStartTime === null || $report->getEndedAt() < $closestStartTime)
                ) {
                    $closestStartTime = $report->getEndedAt();
                }
            }

            return $closestStartTime ?? null;
        }

        return null;
    }

    public function deleteReport(Report $report, Conference $conference): ?\Exception
    {
        $this->_em->beginTransaction();

        dd($report);

        try {
            $conference->removeUser($report->getUser());
            $this->_em->remove($report);
            $this->_em->flush();
            $this->_em->commit();
        } catch (\Exception $e) {
            $this->_em->rollback();

            return $e;
        }

        return null;
    }

    public function findByConferenceIdAndUserIdNotDeleted(Conference $conference, UserInterface $user): ?array
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.deletedAt IS NULL')
            ->andWhere('r.conference = :conference')
            ->andWhere('r.user = :user')
            ->setParameters([
                'conference' => $conference,
                'user' => $user
            ])
            ->getQuery()
            ->getResult();
    }

    public function fileNameExist(int $reportId): ?array
    {
        return $this->createQueryBuilder('r')
            ->select('r.document')
            ->where('r.id = :reportId')
            ->setParameter('reportId', $reportId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getRandomReport(): ?Report
    {
        $ids = $this->createQueryBuilder('r')
            ->select('r.id')
            ->getQuery()
            ->getArrayResult();

        if (empty($ids)) {
            return null;
        }

        $randomReport = $ids[array_rand($ids)]['id'];

        return $this->find($randomReport);
    }

    public function getAllReportsWithFilters(Conference $conference, IndexReportRequest $filters): array
    {
        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(c.id)')
            ->from(ReportComment::class, 'c')
            ->where('c.deletedAt IS NULL')
            ->andWhere('c.report = r')
            ->getDQL();

        $queryBuilder = $this->createQueryBuilder('r')
            ->select(
                'r.id',
                'r.title',
                'r.description',
                'r.startedAt',
                'r.endedAt',
                "($subQuery) as commentsNumber"
            )
            ->where('r.deletedAt IS NULL')
            ->andWhere('r.conference = :conference')
            ->setParameter('conference', $conference)
            ->orderBy('r.createdAt', 'DESC');

        $startTime = $filters->getStartTime();
        if ($startTime) {
            $queryBuilder
                ->andWhere('r.startedAt >= :startTime')
                ->setParameter('startTime', $startTime);
        }

        $endTime = $filters->getEndTime();
        if ($endTime) {
            $queryBuilder
                ->andWhere('r.endedAt <= :endTime')
                ->setParameter('endTime', $endTime);
        }

        $duration = $filters->getDuration();
        if ($duration) {
            $queryBuilder
                ->andWhere('TIMESTAMPDIFF(MINUTE, r.startedAt, r.endedAt) <= :duration')
                ->setParameter('duration', $duration);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function fullTextSearchByTitle(string $title): ?array
    {
        return $this->createQueryBuilder('r')
            ->select('r.id', 'r.title', 'c.id as conference_id')
            ->leftJoin(Conference::class, 'c', 'WITH', 'r.conference = c')
            ->where('MATCH(r.title) AGAINST(:title) > 0')
            ->setParameter('title', $title)
            ->getQuery()
            ->getResult();
    }
}
