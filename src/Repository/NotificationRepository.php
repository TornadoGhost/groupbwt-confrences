<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function add(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getNotificationsByUser(int $userId)
    {
        $query = $this->createQueryBuilder('n')
            ->select('PARTIAL n.{id, title, message, link, viewed, createdAt}')
            ->leftJoin('n.users', 'u')
            ->where('u.id = :userId')
            ->setParameters(['userId' => $userId])
            ->orderBy('n.createdAt', 'DESC');

        return $query->getQuery()->getResult();
    }

    public function markAllAsViewed(int $userId): void
    {
        // not working
        $sql = "update notification n
                join notification_user pivot on n.id=pivot.notification_id
                set n.viewed = 1
                where n.viewed = 0 and pivot.user_id = $userId";

        $this->_em->getConnection()->executeQuery($sql);
    }

    public function getNotViewedNotificationsCountForUser(int $userId): int
    {
        $query = $this->createQueryBuilder('n')
            ->select('count(n.id) as count')
            ->innerJoin('n.users', 'u')
            ->where('n.viewed = :viewed')
            ->andWhere('u.id = :userId')
            ->setParameters([
                'viewed' => 0,
                'userId' => $userId
            ])
        ;

        $result = $query->getQuery()->getArrayResult();

        return $result[0]['count'];
    }

    public function remove(Notification $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function save(Notification $notification)
    {
        $this->_em->persist($notification);
        $this->_em->flush($notification);
    }
}
