<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function saveUser(UserInterface $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getRandomUser(): ?User
    {
        $ids = $this->createQueryBuilder('u')
            ->select('u.id')
            ->getQuery()
            ->getArrayResult();

        return $this->selectRandomUserFromArray($ids);
    }

    public function getRandomUserWithoutAdmin(): ?User
    {
        $ids = $this->createQueryBuilder('u')
            ->select('u.id')
            ->where('JSON_CONTAINS(u.roles, :role) = 0')
            ->setParameter('role', json_encode('ROLE_ADMIN'))
            ->getQuery()
            ->getArrayResult()
        ;

        return $this->selectRandomUserFromArray($ids);
    }

    public function selectRandomUserFromArray(array $ids): ?User
    {
        if (empty($ids)) {
            return null;
        }

        $randomUser = $ids[array_rand($ids)]['id'];

        return $this->find($randomUser);
    }
}
