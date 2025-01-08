<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Form\ConferenceFiltersType;
use App\Form\ConferenceType;
use App\Form\ReportFiltersType;
use App\Service\ConferenceService;
use App\Service\ReportService;
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
    private ConferenceService $conferenceService;

    public function __construct(
        ConferenceService $conferenceService
    )
    {
        $this->conferenceService = $conferenceService;
    }

    /**
     * @Route("/", name="app_conference_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $userId = !$this->getUser() ? null : $this->getUser()->getId();
        $perPage = ConferenceService::COUNT_PER_PAGE;
        $page = $request->query->getInt('page', 1);

        $filtersForm = $this->createForm(ConferenceFiltersType::class);
        $filtersForm->handleRequest($request);

        if ($filtersForm->isSubmitted()) {
            $conferences = $this->conferenceService->getAllConferencesWithFiltersPaginate(
                $perPage,
                $page,
                $userId,
                $filtersForm->getData()
            );
        } else {
            $conferences = $this->conferenceService->getAllConferencesWithFiltersPaginate($perPage, $page, $userId);
        }

        return $this->render('conference/index.html.twig', [
            'conferences' => $conferences,
            'filtersForm' => $filtersForm->createView()
        ]);
    }

    /**
     * @Route("/new", name="app_conference_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function new(Request $request): Response
    {
        // TODO: add field endedAt to form
        $conference = new Conference();
        $form = $this->createForm(ConferenceType::class, $conference);
        $this->conferenceService->formPreparation($request, $conference, $form);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->conferenceService->saveFormChanges($form, $conference);

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
    public function show(
        Request       $request,
        Conference    $conference,
        ReportService $reportService
    ): Response
    {
        $requestFormFilters = $this->createForm(ReportFiltersType::class, null, [
            'start_time' => $conference->getStartedAt(),
            'end_time' => $conference->getEndedAt(),
        ]);
        $requestFormFilters->handleRequest($request);

        if ($requestFormFilters->isSubmitted()) {
            $filters = $reportService->prepareReportFilters($requestFormFilters->getData(), $conference);
            $reports = $reportService->getAllReportsWithFilters($conference, $filters);
        } else {
            $reports = $reportService->getAllReportsWithFilters($conference);
        }

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY'],
            'report_form_filters' => $requestFormFilters->createView(),
            'reports' => $reports
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_conference_edit", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function edit(Request $request, Conference $conference): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $this->conferenceService->formPreparation($request, $conference, $form);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->conferenceService->saveFormChanges($form, $conference);

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
    public function delete(Request $request, Conference $conference): Response
    {
        if ($this->isCsrfTokenValid('delete-conference', $request->request->get('token'))) {
            $this->conferenceService->delete($conference);
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
    public function cancel(
        Request    $request,
        Conference $conference
    ): Response
    {
        if ($this->isCsrfTokenValid('cancel-conference', $request->request->get('token'))) {
            $user = $this->getUser();
            $this->conferenceService->removeUserFromConference($conference, $user);
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }
}
