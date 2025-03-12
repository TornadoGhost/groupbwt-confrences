<?php

namespace App\Controller\Api\v1;

use Pusher\Pusher;
use Pusher\PusherException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PusherController extends AbstractController
{
    /**
     * @Route("/api/pusher/say-hello", name="pusher_say_hello", methods={"POST"})
     */
    public function sayHello(Pusher $pusher): Response
    {
        $arrayTest = [
            'message' => 'Welcome to the club, buddy, hehe',
            'created_at' => '2025-10-1',
            'from' => 'Admin'
        ];

        $arrayListTest = [
            'Product1' => 'pr1',
            'Product2' => 'pr2',
            'Product3' => 'pr3',
            'Product4' => 'pr4',
            'Product5' => 'pr5',
        ];

        $pusher->trigger('greetings', 'new-greeting', $arrayListTest);

        return new Response();
    }

    /**
     * @Route("/api/pusher/admin-notify", name="pusher_admin_notify", methods={"POST"})
     */
    public function personalMessage(Pusher $pusher, Request $request): Response
    {
        $user = $this->getUser();
        $arrayTest = [
            'message' => 'Bohdan has broken dev'
        ];

        $pusher->trigger('private-v-chat.' . $user->getId(), 'big-troubles', $arrayTest);
        return new Response();
    }


    /**
     * @Route("/api/pusher/auth", name="pusher_auth", methods={"POST"})
     * @throws PusherException
     */
    public function pusherAuth(Request $request, Pusher $pusher): JsonResponse
    {
        $user = $this->getUser();
        $channelName = $request->get('channel_name');;
        $socketId = $request->get('socket_id');

        if (!$user || !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        if (!str_contains($channelName, 'private-v-chat.' . $user->getId())) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        $auth = json_decode($pusher->authorizeChannel($channelName, $socketId),true);

        return new JsonResponse($auth);
    }
}
