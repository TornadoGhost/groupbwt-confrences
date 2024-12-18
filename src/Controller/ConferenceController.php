<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Service\ConferenceService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conferences")
 */
class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="app_conference_index", methods={"GET"})
     */
    public function index(Request $request, ConferenceService $service): Response
    {
        $userId = !$this->getUser() ? null : $this->getUser()->getId();
        $conferences = $service->getAllConferenceWithSpecificUserPaginate(
            $userId,
            ConferenceService::COUNT_PER_PAGE,
            $request->query->getInt('page', 1)
        );

        return $this->render('conference/index.html.twig', [
            'conferences' => $conferences,
        ]);
    }

    /**
     * @Route("/new", name="app_conference_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request, ConferenceService $conferenceService): Response
    {
        $conference = new Conference();
        $form = $this->createForm(ConferenceType::class, $conference);
        $conferenceService->prepareForm($request, $conference, $form);

        if ($form->isSubmitted() && $form->isValid()) {
            $conferenceService->saveFormChanges($form, $conference);

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conference/new.html.twig', [
            'conference' => $conference,
            'form' => $form,
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
        ]);
    }

    /**
     * @Route("/{id}", name="app_conference_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Conference $conference): Response
    {
        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_conference_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, Conference $conference, ConferenceService $conferenceService): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $conferenceService->prepareForm($request, $conference, $form);

        if ($form->isSubmitted() && $form->isValid()) {
            $conferenceService->saveFormChanges($form, $conference);

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conference/edit.html.twig', [
            'conference' => $conference,
            'form' => $form,
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY']
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_conference_delete", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Request $request, Conference $conference, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-conference', $request->request->get('token'))) {
            $entityManager->remove($conference);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/join", name="app_conference_join", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function join(Request $request, Conference $conference, ConferenceService $conferenceService): Response
    {
        if ($this->isCsrfTokenValid('join-conference', $request->request->get('token'))) {
            $conferenceService->addUserToConference($conference, $this->getUser());
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/cancel", name="app_conference_cancel", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function cancel(Request $request, Conference $conference, ConferenceService $conferenceService): Response
    {
        if ($this->isCsrfTokenValid('cancel-conference', $request->request->get('token'))) {
            $conferenceService->removeUserFromConference($conference, $this->getUser());
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }
}
