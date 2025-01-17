<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conference;
use App\Entity\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Conference>
 *
 * @method Conference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conference[]    findAll()
 * @method Conference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConferenceRepository extends ServiceEntityRepository
{
    private const MINIMUM_REPORT_TIME_MINUTES = 15;
    private const REPORT_SELECT_NUMBER = '1';

    protected ReportRepository $reportRepository;

    public function __construct(
        ManagerRegistry  $registry,
        ReportRepository $reportRepository
    )
    {
        parent::__construct($registry, Conference::class);
        $this->reportRepository = $reportRepository;
    }

    public function getAllConferencesWithFiltersPaginate(?int $userId = null, array $filters = []): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('c')
            ->where('c.deletedAt IS NULL')
            ->orderBy('c.createdAt', 'ASC');

        if (!$userId) {
            $queryBuilder->leftJoin('c.users', 'u');
        } else {
            $queryBuilder->leftJoin('c.users', 'u', Join::WITH, 'u.id = :userId')
                ->setParameter('userId', $userId);
        }

        if ($filters['report_number'] ?? null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    '(SELECT COUNT(r.id) FROM ' . Report::class . ' r WHERE r.conference = c.id)',
                    $filters['report_number']
                )
            );
        }

        if ($filters['start_date'] ?? null) {
            $queryBuilder->andWhere('c.startedAt = :started_at')
                ->setParameter('started_at', $filters['start_date']);
        }

        if ($filters['end_date'] ?? null) {
            $queryBuilder->andWhere('c.endedAt = :ended_at')
                ->setParameter('ended_at', $filters['end_date']);
        }

        if ($filters['is_available'] ?? null) {
            $subQueryBeforeFirst = $this->getEntityManager()->createQueryBuilder()
                ->select(self::REPORT_SELECT_NUMBER)
                ->from(Report::class, 'r1')
                ->where('r1.conference = c.id')
                ->andWhere('TIMESTAMPDIFF(MINUTE, c.startedAt, r1.startedAt) >= ' . self::MINIMUM_REPORT_TIME_MINUTES);

            $subQueryBetween = $this->getEntityManager()->createQueryBuilder()
                ->select(self::REPORT_SELECT_NUMBER)
                ->from(Report::class, 'r2')
                ->innerJoin(Report::class, 'r3', 'WITH', 'r2.conference = r3.conference AND r2.endedAt <= r3.startedAt')
                ->where('r2.conference = c.id')
                ->andWhere('TIMESTAMPDIFF(MINUTE, r2.endedAt, r3.startedAt) >= ' . self::MINIMUM_REPORT_TIME_MINUTES);

            $subQueryAfterLast = $this->getEntityManager()->createQueryBuilder()
                ->select(self::REPORT_SELECT_NUMBER)
                ->from(Report::class, 'r4')
                ->where('r4.conference = c.id')
                ->andWhere('TIMESTAMPDIFF(MINUTE, r4.endedAt, c.endedAt) >= ' . self::MINIMUM_REPORT_TIME_MINUTES);

            $queryBuilder
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBeforeFirst->getDQL())),
                    $queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryBetween->getDQL())),
                    $queryBuilder->expr()->not($queryBuilder->expr()->exists($subQueryAfterLast->getDQL())),
                ));
        }

        return $queryBuilder;
    }

    public function addUserToConference(Conference $conference, UserInterface $user): void
    {
        $conference->addUser($user);
        $this->saveData($user);
    }

    public function removeUserFromConference(Conference $conference, UserInterface $user): void
    {
        $reports = $this->reportRepository->findByConferenceIdAndUserIdNotDeleted($conference, $user);

        if ($reports) {
            foreach ($reports as $report) {
                $this->_em->remove($report);
            }
        }

        $user->removeConference($conference);
        $this->saveData($user);
    }

    public function saveEditFormChanges(Conference $conference, array $address): Conference
    {
        $conference->setAddress($address);
        $this->saveData($conference);

        return $conference;
    }

    protected function saveData(object $data): void
    {
        $this->_em->persist($data);
        $this->_em->flush();
    }

    public function getRandomConference(): ?Conference
    {
        $ids = $this->createQueryBuilder('c')
            ->select('c.id')
            ->getQuery()
            ->getArrayResult();

        if (empty($ids)) {
            return null;
        }

        $randomId = $ids[array_rand($ids)]['id'];

        return $this->find($randomId);
    }

    public function findParticipantByUserId(int $userId, int $conferenceId): ?array
    {
        return $this->createQueryBuilder('c')
            ->select('u.email')
            ->join('c.users', 'u')
            ->where('u.id = :userId')
            ->andWhere('c.id = :conferenceId')
            ->setParameter('userId', $userId)
            ->setParameter('conferenceId', $conferenceId)
            ->getQuery()
            ->getResult();
    }

    public function fullTextSearchByTitle(string $title): ?array
    {
        return $this->createQueryBuilder('c')
            ->select('c.id', 'c.title')
            ->where('MATCH(c.title) AGAINST(:title) > 0')
            ->setParameter('title', $title)
            ->getQuery()
            ->getResult();
    }

    public function delete(Conference $conference): void
    {
        $this->_em->remove($conference);
        $this->_em->flush();
    }
}
