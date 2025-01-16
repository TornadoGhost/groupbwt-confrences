<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Report;
use App\Entity\ReportComment;
use App\Form\ReportCommentType;
use App\Form\ReportType;
use App\Service\ConferenceService;
use App\Service\ReportCommentService;
use App\Service\ReportService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     * @Route("/new", name="app_report_new", methods={"GET","POST"})
     * @Security("is_granted('ROLE_ANNOUNCER')")
     */
    public function new(
        Request           $request,
        Conference        $conference
    ): Response
    {
        $userPartOfConference = $this->conferenceService->findParticipantByUserId(
            $this->getUser()->getId(),
            $conference->getId()
        );

        if ($userPartOfConference) {
            $this->flashBag->add('error', 'You have already joined the conference');

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        $report = new Report();
        $form = $this->createForm(ReportType::class, $report, [
            'conference_id' => $conference->getId(),
            'conference_start' => $conference->getStartedAt(),
            'conference_end' => $conference->getEndedAt()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form->get('document')->getData() ?? null;
            $user = $this->getUser();
            $result = $this->reportService->save($report, $conference, $user, $document);

            if (!$result) {
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
     * @Route("/{report_id}", name="app_report_show", methods={"GET", "POST"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(
        Request $request,
        Conference $conference,
        Report $report,
        ReportCommentService $reportCommentService
    ): Response
    {
        $comment = new ReportComment();
        $commentForm = $this->createForm(ReportCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $reportCommentService->createReportComment($this->getUser(), $report, $comment);
        }

        return $this->render('report/show.html.twig', [
            'report' => $report,
            'conferenceId' => $conference->getId(),
            'commentForm' => $commentForm->createView()
        ]);
    }

    /**
     * @Route("/{report_id}/edit", name="app_report_edit", methods={"GET","POST"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     * @IsGranted("EDIT", subject="report")
     */
    public function edit(Request $request, Conference $conference, Report $report): Response
    {
        $form = $this->createForm(ReportType::class, $report, [
            'conference_id' => $conference->getId(),
            'conference_start' => $conference->getStartedAt(),
            'conference_end' => $conference->getEndedAt()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $document = $form['document']->getData() ?? null;
            $user = $this->getUser();
            $result = $this->reportService->save($report, $conference, $user, $document);

            if (!$result) {
                return $this->renderForm('report/edit.html.twig', [
                    'report' => $report,
                    'form' => $form,
                ]);
            }

            return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('report/edit.html.twig', [
            'report' => $report,
            'form' => $form,
            'conferenceId' => $conference->getId()
        ]);
    }

    /**
     * @Route("/{report_id}/delete", name="app_report_delete", methods={"POST"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     * @IsGranted("DELETE", subject="report")
     */
    public function delete(Request $request, Conference $conference, Report $report): Response
    {
        if ($this->isCsrfTokenValid('delete' . $report->getId(), $request->request->get('token'))) {
            $result = $this->reportService->deleteReport($report, $conference);

            if ($result) {
                $this->flashBag->add('edit-page-error', 'Error. ' . $result->getMessage());

                return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('app_conference_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{report_id}/{file_name}", name="app_report_file_download", methods={"GET"})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     * @Security("is_granted('ROLE_USER')")
     */
    public function download(string $file_name): StreamedResponse
    {
        return $this->reportService->downloadFile($file_name);
    }
}
