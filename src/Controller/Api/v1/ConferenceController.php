<?php

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Service\ConferenceService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/conferences", name="api_")
 */
class ConferenceController extends AbstractController
{
    private ConferenceService $conferenceService;

    public function __construct(
        ConferenceService $conferenceService
    )
    {
        $this->conferenceService = $conferenceService;
    }

    /**
     * @Route("/", name="conference_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $perPage = ConferenceService::COUNT_PER_PAGE;
        $page = $request->query->getInt('page', 1);
        $userId = !$this->getUser() ? null : $this->getUser()->getId();
        $conferences = $this->conferenceService->getAllConferencesWithFiltersPaginateApi(
            $perPage,
            $page,
            $userId,
            $request->query->all()
        );

        return $this->json($conferences, 200, [], ['groups' => ['api_conferences_all']]);
    }

    /**
     * @Route("", name="conference_store", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function store(Request $request): Response
    {
        $conference = new Conference();
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            $conference = $this->conferenceService->saveFormChanges($form, $conference);

            return new JsonResponse([
                'id' => $conference->getId(),
                'title' => $conference->getTitle(),
                'address' => $conference->getAddress(),
                'country' => $conference->getCountry(),
                'started_at' => $conference->getStartedAt(),
                'ended_at' => $conference->getEndedAt(),
                'created_at' => $conference->getCreatedAt()
            ], 201);
        }

        $errors = [];

        foreach ($form->all() as $fieldName => $formField) {
            foreach ($formField->getErrors() as $error) {
                $errors[$fieldName][] = $error->getMessage();
            }
        }

        return new JsonResponse(['errors' => $errors], 422);
    }

    /**
     * @Route("/{id}", name="conference_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Conference $conference): Response
    {
        return new JsonResponse([
            'id' => $conference->getId(),
            'title' => $conference->getTitle(),
            'address' => $conference->getAddress(),
            'country' => $conference->getCountry(),
            'started_at' => $conference->getStartedAt()->format('Y-m-d H:i'),
            'ended_at' => $conference->getEndedAt()->format('Y-m-d H:i')
        ]);
    }

    /**
     * @Route("/{id}", name="conference_update", methods={"PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function update(Request $request, Conference $conference): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            $conference = $this->conferenceService->saveFormChanges($form, $conference);

            return new JsonResponse([
                'id' => $conference->getId(),
                'title' => $conference->getTitle(),
                'address' => $conference->getAddress(),
                'country' => $conference->getCountry(),
                'started_at' => $conference->getStartedAt()->format('Y-m-d H:i'),
                'ended_at' => $conference->getEndedAt()->format('Y-m-d H:i')
            ]);
        }

        return new JsonResponse([
            'errors' => $this->conferenceService->getFormErrors($form)
        ], 422);
    }

    /**
     * @Route("/{id}", name="conference_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Conference $conference): Response
    {
        $this->conferenceService->delete($conference);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/join", name="conference_join", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function join(Conference $conference, ConferenceService $conferenceService): Response
    {
        $conferenceService->addUserToConference($conference, $this->getUser());

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/cancel", name="conference_cancel", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function cancel(Conference $conference): Response
    {
        $this->conferenceService->removeUserFromConference($conference, $this->getUser());

        return new JsonResponse(null, 204);
    }
}
