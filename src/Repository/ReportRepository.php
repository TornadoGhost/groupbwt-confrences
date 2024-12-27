<?php

namespace App\Repository;

use App\Entity\Conference;
use App\Entity\Report;
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
            ->andWhere('r.deletedAt IS NULL');

        if ($reportId) {
            $existingReportsBuilder
                ->andWhere('r.id != :reportId')
                ->setParameters([
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'conferenceId' => $conferenceId,
                    'reportId' => $reportId
                ]);
        } else {
            $existingReportsBuilder
                ->setParameters([
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'conferenceId' => $conferenceId,
                ]);
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

    public function deleteReport(Report $report, Conference $conference, UserInterface $user): ?string
    {
        $this->_em->beginTransaction();

        try {
            $this->_em->remove($report);
            $conference->removeUser($user);
            $this->_em->flush();
            $this->_em->commit();
        } catch (\Exception $e) {
            $this->_em->rollback();

            return $e->getMessage();
        }

        return null;
    }

    public function findByConferenceIdAndUserIdNotDeleted(Conference $conference, UserInterface $user): ?Report
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
            ->getOneOrNullResult();
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
}
