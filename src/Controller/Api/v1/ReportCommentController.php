<?php

namespace App\Controller\Api\v1;

use App\Entity\Report;
use App\Entity\ReportComment;
use App\Form\ReportCommentType;
use App\Service\ReportCommentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/reports/{id}/comments", name="api_")
 * @Security("is_granted('ROLE_USER')")
 */
class ReportCommentController extends AbstractController
{
    private ReportCommentService $reportCommentService;
    public function __construct(
        ReportCommentService $reportCommentService
    )
    {
        $this->reportCommentService = $reportCommentService;
    }

    /**
     * @Route("", name="report_comments_index", methods={"GET"})
     */
    public function index(
        Request $request,
        Report $report,
        ReportCommentService $commentService
    ): Response
    {
        $comments = $commentService->getCommentsPaginate(
            $report,
            (int) $request->query->get('page', 1),
            $commentService::MAX_PER_PAGE
        );

        return $this->json($comments, Response::HTTP_OK, [], ['groups' => ['api_report_comments']]);
    }

    /**
     * @Route("", name="report_comments_store", methods={"POST"})
     */
    public function store(Request $request, Report $report): Response
    {
        $comment = new ReportComment();
        $commentForm = $this->createForm(ReportCommentType::class, $comment);
        $commentForm->submit($request->toArray());

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $report = $this->reportCommentService->createReportCommentApi($this->getUser(), $report, $comment);

            return $this->json($report, Response::HTTP_CREATED, [], ['groups' => ['api_report_comments']]);
        }

        $formErrors = $this->reportCommentService->getFormErrors($commentForm);

        return $this->json(['errors' => $formErrors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_show", methods={"GET"})
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     */
    public function show(ReportComment $reportComment): Response
    {
        return $this->json($reportComment, Response::HTTP_OK, [], ['groups' => ['api_report_comments']]);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_update", methods={"PATCH"})
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     */
    public function update(Request $request, ReportComment $reportComment):Response
    {
        $commentForm = $this->createForm(ReportCommentType::class, $reportComment);
        $commentForm->submit($request->toArray());

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->reportCommentService->save($reportComment, true);

            return $this->json($reportComment, Response::HTTP_OK, [], ['groups' => ['api_report_comments']]);
        }

        $formErrors = $this->reportCommentService->getFormErrors($commentForm);

        return $this->json(['errors' => $formErrors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_delete", methods={"DELETE"})
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     */
    public function delete(ReportComment $reportComment): Response
    {
        $this->reportCommentService->removeComment($reportComment, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/load", name="app_report_comments_load", methods={"GET"})
     */
    public function loadComments(
        Request $request,
        Report $report,
        ReportCommentService $commentService
    ): JsonResponse
    {
        $comments = $commentService->getCommentsByPage(
            $report,
            $report->getConference()->getId(),
            (int) $request->query->get('page', 1),
            $commentService::MAX_PER_PAGE
        );

        return $this->json($comments);
    }
}
