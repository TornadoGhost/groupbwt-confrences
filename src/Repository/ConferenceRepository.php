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
}
