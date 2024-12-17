<?php

namespace App\Repository;

use App\Entity\Conference;
use App\Entity\User;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conference::class);
    }

    public function getAllConferencesWithSpecificUser($userId): QueryBuilder
    {
        if (!$userId) {
            return $this->createQueryBuilder('c')
                ->select('c, u')
                ->leftJoin('c.users', 'u')
                ->where("c.deletedAt IS NULL");
        }
        return $this->createQueryBuilder('c')
            ->select('c, u')
            ->leftJoin('c.users', 'u', Join::WITH, 'u.id = :userId')
            ->where("c.deletedAt IS NULL")
            ->setParameter('userId', $userId);
    }

    public function workWithConference($job, Conference $conference, User $user): void
    {
        if ($job === 'add') {
            $conference->addUser($user);
        } else if ($job === 'remove') {
            $conference->removeUser($user);
        }

        $this->_em->persist($conference);
        $this->_em->flush();
    }

    /**
     * @param User|UserInterface $user
     */
    public function addUserToConference(Conference $conference, $user): void
    {
        $this->workWithConference('add', $conference, $user);
    }

    /**
     * @param User|UserInterface $user
     */
    public function removeUserFromConference(Conference $conference, $user): void
    {
        $this->workWithConference('remove', $conference, $user);
    }
}
