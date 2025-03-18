<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Notification;
use App\Service\NotificationService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


// TODO: check if I can move /api/v1 to global place, so I dont need to write it in every controller

/**
 * @Route("/api/v1/notifications", name="api_")
 * @IsGranted("ROLE_USER")
 */
class NotificationController extends AbstractController
{
    private NotificationService $notificationService;

    public function __construct(
        NotificationService $notificationService
    )
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @Route("/", name="notifications_index", methods={"GET"})
     */
    public function index(NotificationService $notificationService): Response
    {
        return $this->json(
            $notificationService->getAllByUser($this->getUser()->getId()),
            Response::HTTP_OK,
            [],
            [
                'groups' => ['api_notifications_user']
            ]
        );
    }

    /**
     * @Route("/{id}/watched", methods={"PATCH"})
     */

    // TODO: add Voters, so user can change status only for own notifications
    public function watched(Notification $notification): Response
    {
        $this->notificationService->changeWatchStatus($notification);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
