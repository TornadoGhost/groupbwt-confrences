<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Service\ConferenceService;
use Doctrine\ORM\EntityManagerInterface;
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
        return $this->render('conference/index.html.twig', [
            'conferences' => $service->getAllConferenceWithSpecificUserPaginate(
                $this->getUser(),
                15,
                $request->query->getInt('page', 1)
            ),
        ]);
    }

    /**
     * @Route("/new", name="app_conference_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $conference = new Conference();
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($conference);
            $entityManager->flush();

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conference/new.html.twig', [
            'conference' => $conference,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_conference_show", methods={"GET"})
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
     */
    public function edit(Request $request, Conference $conference, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('conference/edit.html.twig', [
            'conference' => $conference,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_conference_delete", methods={"POST"})
     */
    public function delete(Request $request, Conference $conference, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$conference->getId(), $request->request->get('_token'))) {
            $entityManager->remove($conference);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/join", name="app_conference_join", methods={"GET"})
     */
    public function join(Conference $conference, ConferenceService $conferenceService): Response
    {
        $conferenceService->addUserToConference($conference, $this->getUser());

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{id}/cancel", name="app_conference_cancel", methods={"GET"})
     */
    public function cancel(Conference $conference, ConferenceService $conferenceService): Response
    {
        $conferenceService->removeUserFromConference($conference, $this->getUser());

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }
}
