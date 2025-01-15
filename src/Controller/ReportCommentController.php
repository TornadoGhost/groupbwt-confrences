<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Report;
use App\Entity\ReportComment;
use App\Form\ReportCommentType;
use App\Service\ReportCommentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conferences/{conference_id}/reports/{report_id}/comments/{comment_id}")
 * @ParamConverter("conference", options={"mapping": {"conference_id": "id"}})
 * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
 * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
 */
class ReportCommentController extends AbstractController
{
    /**
     * @Route("/delete", name="app_report_comment_delete", methods={"POST"})
     * @IsGranted("DELETE", subject="reportComment")
     */
    public function delete(
        Request              $request,
        Conference           $conference,
        Report               $report,
        ReportComment        $reportComment,
        ReportCommentService $commentService
    ): Response
    {
        $conferenceId = $conference->getId();
        $reportId = $report->getId();

        if ($this->isCsrfTokenValid('delete' . $reportId . $conferenceId, $request->request->get('_token'))) {
            $commentService->removeComment($reportComment, true);
        }

        return $this->redirectToRoute('app_report_show', [
            'conference_id' => $conferenceId,
            'report_id' => $reportId
        ]);
    }

    /**
     * @Route("/edit", name="app_report_comment_edit", methods={"GET","POST"})
     * @IsGranted("EDIT", subject="reportComment")
     */
    public function edit(
        Request              $request,
        Conference           $conference,
        Report               $report,
        ReportComment        $reportComment,
        ReportCommentService $commentService
    )
    {
        $form = $this->createForm(ReportCommentType::class, $reportComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentService->updateComment($reportComment);

            return $this->redirectToRoute('app_report_show', [
                'conference_id' => $conference->getId(),
                'report_id' => $report->getId()
            ]);
        }

        return $this->render('comment/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
