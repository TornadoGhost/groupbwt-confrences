<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Service\ConferenceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseConferenceController extends AbstractController
{
    const CREATE = 'Create';
    const EDIT = 'Edit';
    protected ConferenceService $conferenceService;

    public function __construct(
        ConferenceService $conferenceService
    )
    {
        $this->conferenceService = $conferenceService;
    }

    protected function handleForm(
        Request $request,
        Conference $conference,
        string $method,
        string $alertSuccessMessage
    ): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $address = $this->conferenceService->getAddressFromConference($conference);

        if (!empty($address)) {
            foreach ($address as $key => $value) {
                $form->get($key)->setData($value);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->conferenceService->saveFormChanges(
                $conference,
                [
                    'latitude' => $form->get('latitude')->getData(),
                    'longitude' => $form->get('longitude')->getData()
                ]
            );

            $this->addFlash('success', $alertSuccessMessage);

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        $renderForm = null;

        if ($method === self::CREATE) {
            $renderForm = $this->renderForm('conference/new.html.twig', [
                'conference' => $conference,
                'form' => $form,
                'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
            ]);
        } else if ($method === self::EDIT) {
            $renderForm = $this->renderForm('conference/edit.html.twig', [
                'conference' => $conference,
                'form' => $form,
                'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
            ]);
        }

        return $renderForm;
    }
}
