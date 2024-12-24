<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Report;
use App\Form\ReportType;
use App\Repository\ConferenceRepository;
use App\Repository\ReportRepository;
use App\Service\ConferenceService;
use App\Service\ReportService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conferences/{conference_id}/reports")
 * @ParamConverter("conference", options={"mapping": {"conference_id": "id"}})
 */
class ReportController extends AbstractController
{
    protected ReportService $reportService;
    protected ConferenceService $conferenceService;
    protected FlashBagInterface $flashBag;

    public function __construct(
        ReportService     $reportService,
        ConferenceService $conferenceService,
        FlashBagInterface $flashBag
    )
    {
        $this->reportService = $reportService;
        $this->conferenceService = $conferenceService;
        $this->flashBag = $flashBag;
    }

    /**
     * @Route("/", name="app_report_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('report/index.html.twig', [
            'reports' => $this->reportService->getAllReports(),
        ]);
    }

    /**
     * @Route("/new", name="app_report_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ANNOUNCER')")
     */
    public function new(
        Request           $request,
        Conference        $conference
    ): Response
    {
        $userId = $this->getUser()->getId();
        $conferenceId = $conference->getId();
        $userPartOfConference = $this->conferenceService->findParticipantByUserId($userId, $conferenceId);

        if ($userPartOfConference) {
            $this->flashBag->add('error', 'You have already joined the conference');

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        $report = new Report();
        $form = $this->reportService->prepareForm(
            $report,
            $request,
            $conference
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->get('document')->getData() ?? null;
            $user = $this->getUser();
            $result = $this->reportService->saveReportWithFile($report, $conference, $user, $document);

            if (!$result) {
                $this->flashBag->add(
                    'upload-file-error',
                    'File upload error. Try again later.'
                );

                return $this->renderForm('report/new.html.twig', [
                    'report' => $report,
                    'form' => $form,
                ]);
            }

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('report/new.html.twig', [
            'report' => $report,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{report_id}", name="app_report_show", methods={"GET"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     */
    public function show(Report $report): Response
    {
        return $this->render('report/show.html.twig', [
            'report' => $report,
        ]);
    }

    /**
     * @Route("/{report_id}/edit", name="app_report_edit", methods={"GET","POST"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     */
    public function edit(Request $request, Report $report, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_report_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('report/edit.html.twig', [
            'report' => $report,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{report_id}", name="app_report_delete", methods={"POST"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     */
    public function delete(Request $request, Report $report, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $report->getId(), $request->request->get('_token'))) {
            $entityManager->remove($report);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_report_index', [], Response::HTTP_SEE_OTHER);
    }
}
