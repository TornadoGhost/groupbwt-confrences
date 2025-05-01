<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Notification;
use App\Service\NotificationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;


// TODO: check if I can move /api/v1 to global place, so I dont need to write it in every controller (can via routes, check comments there)

/**
 * @Route("/api/v1/notifications", name="api_")
 * @Security("is_granted('ROLE_USER')")
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
     * @Route("/{id}/viewed", methods={"PATCH"})
     * @IsGranted("EDIT", subject="notification")
     */
    public function viewed(Notification $notification): Response
    {
        $this->notificationService->changeWatchStatus($notification);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/mark-viewed", methods={"PATCH"})
     */
    public function markAllAsViewed(): Response
    {
        if ($this->notificationService->markAllAsViewed($this->getUser())) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        throw new HttpException(Response::HTTP_CONFLICT, 'All notifications already viewed');
    }
}
