<?php

namespace App\Repository;

use App\Entity\Conference;
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
    protected ReportRepository $reportRepository;

    public function __construct(
        ManagerRegistry $registry,
        ReportRepository $reportRepository
    )
    {
        parent::__construct($registry, Conference::class);
        $this->reportRepository = $reportRepository;
    }

    public function getAllConferencesWithSpecificUser(?int $userId = null): QueryBuilder
    {
        if (!$userId) {
            return $this->createQueryBuilder('c')
                ->select('c, u')
                ->leftJoin('c.users', 'u')
                ->where("c.deletedAt IS NULL")
                ->orderBy('c.createdAt', 'DESC')
                ;
        }
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->leftJoin('c.users', 'u', Join::WITH, 'u.id = :userId')
            ->where("c.deletedAt IS NULL")
            ->setParameter('userId', $userId)
            ->orderBy('c.createdAt', 'DESC')
            ;
    }

    public function addUserToConference(Conference $conference, UserInterface $user): void
    {
        $conference->addUser($user);
        $this->saveData($user);
    }

    public function removeUserFromConference(Conference $conference, UserInterface $user): void
    {
        $report = $this->reportRepository->findByConferenceIdAndUserIdNotDeleted($conference, $user);

        if ($report) {
            $conference->removeReport($report);
        }

        $conference->removeUser($user);
        $this->saveData($user);
    }

    public function saveEditFormChanges(Conference $conference, array $address): void
    {
        $conference->setAddress($address);
        $this->saveData($conference);
    }

    protected function saveData(object $data): void
    {
        $this->_em->persist($data);
        $this->_em->flush();
    }

    public function getRandomConference(): ?Conference
    {
        $ids = $this->_em->createQuery('SELECT u.id FROM App\Entity\Conference u')
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
}
