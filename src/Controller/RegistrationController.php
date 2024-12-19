<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\RegistrationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends BaseAuthController
{
    /**
     * @Route(path="/register", name="app_register")
     */
    public function register(
        Request                     $request,
        RegistrationService         $registrationService
    ): Response
    {
        if ($this->getUser()) {
            return $this->refererRedirect($request);
        }

        $user = new User();
        $form = $registrationService->userFormPreparation($request, $user);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationService->saveNewUser($user, $form);

            return $this->redirectToRoute('app_conference_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
