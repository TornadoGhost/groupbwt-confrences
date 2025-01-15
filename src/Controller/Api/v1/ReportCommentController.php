<?php

namespace App\Controller\Api\v1;

use App\Entity\Report;
use App\Entity\ReportComment;
use App\Form\ReportCommentType;
use App\Service\ReportCommentService;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/v1/reports/{id}/comments", name="api_")
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
     *
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Pagination page",
     *     @OA\Schema(type="integer"),
     *     example="1")
     * @OA\Response(response="200", description="Got paginated comments for specific report",
     *      @OA\JsonContent(type="object", ref=@Model(type=ReportComment::class, groups={"api_report_comments_index"})))
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="500", description="Server error")
     */
    public function index(
        Request              $request,
        Report               $report,
        ReportCommentService $commentService
    ): Response
    {
        $comments = $commentService->getCommentsPaginate(
            $report,
            (int)$request->query->get('page', 1),
            $commentService::MAX_PER_PAGE
        );

        return $this->json($comments, Response::HTTP_OK, [], ['groups' => ['api_report_comments_index']]);
    }

    /**
     * @Route("", name="report_comments_store", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="201", description="Created a comment for a specific report",
     *     @OA\JsonContent(type="object", ref=@Model(type=ReportComment::class, groups={"api_report_comments_index"})))
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="500", description="Server error")
     */
    public function store(Request $request, Report $report): Response
    {
        $comment = new ReportComment();
        $commentForm = $this->createForm(ReportCommentType::class, $comment);
        $commentForm->submit($request->toArray());

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $report = $this->reportCommentService->createReportComment($this->getUser(), $report, $comment);

            return $this->json($report, Response::HTTP_CREATED, [], ['groups' => ['api_report_comments_index']]);
        }

        $formErrors = $this->reportCommentService->getFormErrors($commentForm);

        return $this->json(['errors' => $formErrors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_show", methods={"GET"})
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="200", description="Showed the specified comment for the specific report",
     *      @OA\JsonContent(type="object", ref=@Model(type=ReportComment::class, groups={"api_report_comments_index"})))
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="404", description="The requested resource could not be found")
     * @OA\Response(response="500", description="Server error")
     */
    public function show(ReportComment $reportComment): Response
    {
        return $this->json($reportComment, Response::HTTP_OK, [], ['groups' => ['api_report_comments_index']]);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_update", methods={"PATCH"})
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     * @IsGranted("EDIT", subject="reportComment")
     *
     * @OA\Response(response="200", description="Updated the specified comment for the specific report",
     *       @OA\JsonContent(type="object", ref=@Model(type=ReportComment::class, groups={"api_report_comments_index"}))
     * )
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="404", description="The requested resource could not be found")
     * @OA\Response(response="500", description="Server error")
     */
    public function update(Request $request, ReportComment $reportComment): Response
    {
        $commentForm = $this->createForm(ReportCommentType::class, $reportComment);
        $commentForm->submit($request->toArray());

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->reportCommentService->save($reportComment, true);

            return $this->json($reportComment, Response::HTTP_OK, [], ['groups' => ['api_report_comments_index']]);
        }

        $formErrors = $this->reportCommentService->getFormErrors($commentForm);

        return $this->json(['errors' => $formErrors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{comment_id}", name="report_comments_delete", methods={"DELETE"})
     * @ParamConverter("reportComment", options={"mapping": {"comment_id": "id"}})
     * @IsGranted("DELETE", subject="reportComment")
     *
     * @OA\Response(response="204", description="Deleted the specified comment for the specific report",
     *        @OA\JsonContent(type="object", ref=@Model(type=ReportComment::class, groups={"api_report_comments_index"})
     *              )
     * )
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="404", description="The requested resource could not be found")
     * @OA\Response(response="500", description="Server error")
     */
    public function delete(ReportComment $reportComment): Response
    {
        $this->reportCommentService->removeComment($reportComment, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/load", name="report_comments_load", methods={"GET"})
     */
    public function loadComments(
        Request              $request,
        Report               $report,
        ReportCommentService $commentService
    ): JsonResponse
    {
        $comments = $commentService->getCommentsByPage(
            $report,
            $report->getConference()->getId(),
            (int)$request->query->get('page', 1),
            $commentService::MAX_PER_PAGE
        );

        return $this->json($comments);
    }
}
