<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\DTO\Request\IndexReportCommentRequest;
use App\DTO\Request\ReportCommentRequest;
use App\Entity\Report;
use App\Entity\ReportComment;
use App\Service\ReportCommentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/reports/{id<\d+>}/comments", name="api_")
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
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(
        IndexReportCommentRequest $request,
        Report                    $report,
        ReportCommentService      $commentService
    ): Response
    {
        return $this->json(
            $commentService->getCommentsPaginate(
                $report,
                (int)$request->getPage() ?: 1,
                $commentService::MAX_PER_PAGE
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['api_report_comments_index']]);
    }

    /**
     * @Route("", name="report_comments_store", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function store(ReportCommentRequest $request, Report $report): Response
    {
        return $this->json(
            $this->reportCommentService->createComment($request, $report, $this->getUser()),
            Response::HTTP_CREATED,
            [],
            ['groups' => ['api_report_comments_index']]
        );
    }

    /**
     * @Route("/{comment_id}", name="report_comments_show", methods={"GET"}, priority=1)
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(ReportComment $reportComment): Response
    {
        return $this->json($reportComment, Response::HTTP_OK, [], ['groups' => ['api_report_comments_index']]);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_update", methods={"PATCH"}, priority=1)
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     * @IsGranted("EDIT", subject="reportComment")
     */
    public function update(ReportCommentRequest $request, ReportComment $reportComment): Response
    {
        return $this->json(
            $this->reportCommentService->updateComment($request, $reportComment),
            Response::HTTP_OK,
            [],
            ['groups' => ['api_report_comments_index']]
        );
    }

    /**
     * @Route("/{comment_id}", name="report_comments_delete", methods={"DELETE"}, priority=1)
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     * @IsGranted("DELETE", subject="reportComment")
     */
    public function delete(ReportComment $reportComment): Response
    {
        $this->reportCommentService->removeComment($reportComment, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/load", name="report_comments_load", methods={"GET"}, priority=2)
     */
    public function loadComments(
        Request              $request,
        Report               $report,
        ReportCommentService $commentService
    ): JsonResponse
    {
        $user = $this->getUser() ?? null;
        $userId = $user ? $user->getId() : null;
        $comments = $commentService->getCommentsByPage(
            $report,
            $userId,
            $report->getConference()->getId(),
            $commentService::MAX_PER_PAGE,
            (int)$request->query->get('page', 1)
        );

        return $this->json($comments, Response::HTTP_OK);
    }
}
