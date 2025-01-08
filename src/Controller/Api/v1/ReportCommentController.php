<?php

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Entity\Report;
use App\Service\ReportCommentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class ReportCommentController extends AbstractController
{
    /**
     * @Route(
     *     "/conferences/{conference_id}/reports/{report_id}/comments/load",
     *     name="app_report_comments_load", methods={"GET"}
     *     )
     * @ParamConverter("conference", options={"mapping": {"conference_id": "id"}})
     * @ParamConverter("report", options={"mapping": {"report_id": "id"}})
     * @Security("is_granted('ROLE_USER')")
     */

    public function loadComments(
        Request $request,
        Conference $conference,
        Report $report,
        ReportCommentService $commentService
    ): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $conferenceId = $conference->getId();

        $comments = $commentService->getCommentsByPage(
            $report,
            $conferenceId,
            (int) $page,
            $commentService::MAX_PER_PAGE
        );

        return new JsonResponse($comments);
    }
}
