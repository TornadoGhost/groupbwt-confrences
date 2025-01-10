<?php

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Service\ConferenceService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/", name="conferences_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $userId = !$this->getUser() ? null : $this->getUser()->getId();
        $conferences = $this->conferenceService->getAllConferencesWithFiltersPaginateApi(
            ConferenceService::COUNT_PER_PAGE,
            $request->query->getInt('page', 1),
            $userId,
            $request->query->all()
        );

        return $this->json($conferences, Response::HTTP_OK, [], ['groups' => ['api_conferences_all']]);
    }

    /**
     * @Route("", name="conferences_store", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function store(Request $request): Response
    {
        $conference = new Conference();
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            $conference = $this->conferenceService->saveFormChanges($form, $conference);

            return $this->json($conference, Response::HTTP_CREATED, ['groups' => ['api_conferences_store']]);
        }

        $errors = $this->conferenceService->getFormErrors($form);

        return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="conferences_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Conference $conference): Response
    {
        return $this->json($conference, Response::HTTP_OK, [], ['groups' => ['api_conferences_show']]);
    }

    /**
     * @Route("/{id}", name="conferences_update", methods={"PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function update(Request $request, Conference $conference): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            $conference = $this->conferenceService->saveFormChanges($form, $conference);

            return $this->json($conference, Response::HTTP_OK, [], ['groups' => ['api_conferences_show']]);
        }

        return $this->json([
            'errors' => $this->conferenceService->getFormErrors($form)
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="conferences_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Conference $conference): Response
    {
        $this->conferenceService->delete($conference);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/join", name="conferences_join", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function join(Conference $conference, ConferenceService $conferenceService): Response
    {
        $conferenceService->addUserToConference($conference, $this->getUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/cancel", name="conferences_cancel", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function cancel(Conference $conference): Response
    {
        $this->conferenceService->removeUserFromConference($conference, $this->getUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
