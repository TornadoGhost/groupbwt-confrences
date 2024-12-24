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
        \DateTime $startTime,
        \DateTime $endTime,
        int $conferenceId
    ): ?\DateTimeInterface
    {
        $existingReports = $this->createQueryBuilder('r')
            ->join('r.conference', 'c')
            ->where('r.startedAt < :endTime')
            ->andWhere('r.endedAt > :startTime')
            ->andWhere('c.id = :conferenceId')
            ->andWhere('r.deletedAt IS NOT NULL')
            ->setParameters([
                'startTime' => $startTime,
                'endTime' => $endTime,
                'conferenceId' => $conferenceId,
            ])
            ->getQuery()
            ->getResult();

        if (count($existingReports) > 0) {
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
}
