<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationService
{
    private NotificationRepository $notificationRepository;
    private UserRepository $userRepository;
    private Pusher $pusher;

    public function __construct(
        NotificationRepository $notificationRepository,
        UserRepository         $userRepository,
        Pusher                 $pusher
    )
    {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
        $this->pusher = $pusher;
    }

    public function saveNotification(
        string  $title,
        string  $message,
        int     $userId,
        ?string $link = null
    ): Notification
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        $notification = new Notification();
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->addUser($user);

        if ($link !== null) {
            $notification->setLink($link);
        }

        $this->notificationRepository->save($notification);

        return $notification;
    }

    public function pushNotification(
        string $event,
        string $title,
        string $message,
        int    $userId,
        array  $channels = ['notification'],
        string $link = null
    )
    {
        $this->saveNotification($title, $message, $userId, $link);
        $message = [
            'title' => $title,
            'message' => $message,
            'createdAt' => (new \DateTime())->format('Y-m-d H:i')
        ];
        $this->pusher->trigger($channels, $event, $message);
    }

    public function getAllByUser(int $userId)
    {
        return $this->notificationRepository->getNotificationsByUser($userId);
    }

    public function changeWatchStatus(Notification $notification)
    {
        if ($notification->isViewed()) {
            throw new HttpException(Response::HTTP_CONFLICT, 'Watched status already true.');
        }

        $notification->setViewed(true);

        $this->notificationRepository->save($notification);
    }
}
