<?php

namespace App\Controller\Api\v1;

use App\Service\Mailer\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestMailController extends AbstractController
{
    /**
     * @Route("/test-email", name="test_email", methods={"GET"})
     */
    public function index(MailerService $service): Response
    {
        $service->sendEmail();

        return new JsonResponse('success');
    }
}
