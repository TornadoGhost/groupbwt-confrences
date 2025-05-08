<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\DTO\Request\IndexReportRequest;
use App\DTO\Request\ReportRequest;
use App\Entity\Conference;
use App\Entity\Report;
use App\Service\ConferenceService;
use App\Service\ReportService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conferences/{conference_id<\d+>}/reports", name="api_", requirements={"id"="\d+"})
 * @ParamConverter("conference", options={"mapping": {"conference_id": "id"}})
 */
class ReportController extends BaseReportController
{
    protected ReportService $reportService;
    protected ConferenceService $conferenceService;

    public function __construct(
        ReportService     $reportService,
        ConferenceService $conferenceService
    )
    {
        parent::__construct($reportService);
        $this->reportService = $reportService;
        $this->conferenceService = $conferenceService;
    }

    /**
     * @Route("", name="reports_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function index(
        IndexReportRequest $request,
        Conference    $conference,
        ReportService $reportService
    ): Response
    {
        return $this->json(
            $reportService->getAllReportsWithFilters($conference, $request),
            Response::HTTP_OK,
            [],
            ['groups' => ['api_reports_all']]
        );
    }

    /**
     * @Route("", name="reports_store", methods={"POST"})
     * @Security("is_granted('ROLE_ANNOUNCER')")
     */

    // TODO: done here, need to continue rework api from forms
    public function store(
        ReportRequest $request,
        Conference $conference
    ): Response
    {
        $user = $this->getUser();
        $userPartOfConference = $this->conferenceService->findParticipantByUserId(
            $user->getId(),
            $conference->getId()
        );

        if ($userPartOfConference) {
            return $this->json(
                ['message' => 'You have already joined the conference'],
                Response::HTTP_CONFLICT
            );
        }
        return $this->saveData(
            $this->reportService->createReport($request),
            $conference,
            $user,
            $request->getDocument()
        );
    }

    /**
     * @Route("/{id}", name="reports_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Report $report): Response
    {
        return $this->json($report, Response::HTTP_OK, [], ['groups' => ['api_reports_show']]);
    }

    /**
     * @Route("/{id}", name="reports_update", methods={"PUT"})
     * @IsGranted("EDIT", subject="report")
     */
    public function update(ReportRequest $request, Report $report, Conference $conference): Response
    {
        return $this->saveData(
            $this->reportService->setReportData($report, $request),
            $conference,
            $this->getUser(),
            $report->getDocument()
        );
    }

    /**
     * @Route("/{id}", name="reports_delete", methods={"DELETE"})
     * @IsGranted("DELETE", subject="report")
     */
    public function delete(Report $report, Conference $conference): Response
    {

        $exception = $this->reportService->deleteReport($report, $conference);

        if ($exception) {
            return $this->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/{file_name}", name="report_file_download", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function download(string $file_name): StreamedResponse
    {
        return $this->reportService->downloadFile($file_name);
    }
}
