<?php

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Entity\Report;
use App\Form\ReportType;
use App\Service\ConferenceService;
use App\Service\ReportService;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/api/v1/conferences/{conference_id}/reports", name="api_")
 * @ParamConverter("conference", options={"mapping": {"conference_id": "id"}})
 */
class ReportController extends AbstractController
{
    protected ReportService $reportService;
    protected ConferenceService $conferenceService;

    public function __construct(
        ReportService     $reportService,
        ConferenceService $conferenceService
    )
    {
        $this->reportService = $reportService;
        $this->conferenceService = $conferenceService;
    }

    /**
     * @Route("", name="reports_index", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Parameter(
     *     name="start_time",
     *     in="query",
     *     description="The start time of a report",
     *     @OA\Schema(type="date_time"),
     *     example="2024-12-27T12:00"
     * )
     * @OA\Parameter(
     *     name="end_time",
     *     in="query",
     *     description="The end time of a report",
     *     @OA\Schema(type="date_time"),
     *     example="2024-12-27T18:00"
     * )
     * @OA\Parameter(
     *     name="duration",
     *     in="query",
     *     description="The duration of the a report in minutes",
     *     @OA\Schema(type="integer"),
     *     example="30"
     * )
     * @OA\Response(response="200", description="Got all reports for specific conference",
     *         @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="id", type="integer", example=455),
     *          @OA\Property(property="title", type="string", example="Soluta sed ipsum consequuntur odio."),
     *          @OA\Property(property="description", type="string", example="Beatae pariatur omnis omnis explicabo dolores pariatur fugit porro."),
     *          @OA\Property(property="startedAt", type="string", format="datetime", example="2024-12-27 17:10"),
     *          @OA\Property(property="endedAt", type="string", format="datetime", example="2024-12-27 16:50"),
     *          @OA\Property(property="commentsNumber", type="integer", example=84)
     *      )
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
    public function index(
        Request $request,
        Conference    $conference,
        ReportService $reportService
    ): Response
    {
        $filters = $request->query->all() ?? [];
        $reports = $reportService->getAllReportsWithFilters($conference, $filters);

        return $this->json($reports, Response::HTTP_OK, [], ['groups' => ['api_reports_all']]);
    }

    /**
     * @Route("", name="reports_store", methods={"POST"})
     * @Security("is_granted('ROLE_ANNOUNCER')")
     *
     * @OA\RequestBody(required=true,
     *       @OA\JsonContent(
     *           type="object",
     *           required={"title", "description", "startedAt", "endedAt"},
     *           @OA\Property(property="title", type="string", example="Title"),
     *           @OA\Property(property="description", type="string", example="Description"),
     *           @OA\Property(property="startedAt", type="string", format="date-time", example="2025-10-01T11:00"),
     *           @OA\Property(property="endedAt", type="string", format="date-time", example="2025-10-01T18:00"),
     *           @OA\Property(property="document", type="file", format="pptx", example="document.pptx")
     *      )
     * )
     * @OA\Response(response="201", description="Created a report for a specific conference",
     *       @OA\JsonContent(type="object", ref=@Model(type=Report::class, groups={"api_reports_store"})))
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="422", description="Form validations errors",
     *        @OA\JsonContent(
     *            type="object",
     *            @OA\Property(
     *                property="errors",
     *                type="object",
     *                @OA\Property(property="title", type="string", example={"The title should be not null"}),
     *                @OA\Property(property="description", type="string", example={"The description should be not null"}),
     *                @OA\Property(property="startedAt", type="string", example={"The start time cannot be blank"}),
     *                @OA\Property(property="endedAt", type="string", example={"The end time cannot be blank"}),
     *                @OA\Property(property="document", type="string",
     *                      example={"The file should be not bigger than 10 mb"})))))
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="404", description="The requested resource could not be found")
     * @OA\Response(response="500", description="Server error")
     */
    public function store(
        Request    $request,
        Conference $conference
    ): Response
    {
        // TODO: make download pptx file (via base64, because other variants not working and postman not working properly too)
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

        $report = new Report();
        $form = $this->createForm(ReportType::class, $report, [
            'conference_id' => $conference->getId(),
            'conference_start' => $conference->getStartedAt(),
            'conference_end' => $conference->getEndedAt()
        ]);

        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->saveData($form, $report, $conference, $user);
        }

        $errors = $this->reportService->getFormErrors($form);

        return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="reports_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="200", description="Showed the specified report for the specific conference",
     *     @OA\JsonContent(type="object", ref=@Model(type=Report::class, groups={"api_reports_show"}))
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
    public function show(Report $report): Response
    {
        return $this->json($report, Response::HTTP_OK, [], ['groups' => ['api_reports_show']]);
    }

    /**
     * @Route("/{id}", name="reports_update", methods={"PUT"})
     * @IsGranted("EDIT", subject="report")
     *
     * @OA\Response(response="200", description="Updated the specified report for a specific conference",
     *        @OA\JsonContent(type="object", ref=@Model(type=Report::class, groups={"api_reports_store"})))
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="422", description="Form validations errors",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="title", type="string", example={"The title should be not null"}),
     *                 @OA\Property(property="description", type="string", example={"The description should be not null"}),
     *                 @OA\Property(property="startedAt", type="string", example={"The start time cannot be blank"}),
     *                 @OA\Property(property="endedAt", type="string", example={"The end time cannot be blank"}),
     *                 @OA\Property(property="document", type="string",
     *                       example={"The file should be not bigger than 10 mb"})))))
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="404", description="The requested resource could not be found")
     * @OA\Response(response="500", description="Server error")
     */
    public function update(Request $request, Report $report, Conference $conference): Response
    {
        $form = $this->createForm(ReportType::class, $report, [
            'conference_id' => $conference->getId(),
            'conference_start' => $conference->getStartedAt(),
            'conference_end' => $conference->getEndedAt()
        ]);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->saveData($form, $report, $conference, $this->getUser());
        }

        return new JsonResponse([
            'errors' => $this->conferenceService->getFormErrors($form)
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="reports_delete", methods={"DELETE"})
     * @IsGranted("DELETE", subject="report")
     *
     * @OA\Response(response="204", description="Deleted the specified report for a specific conference")
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
    public function delete(Report $report, Conference $conference): Response
    {

        $exception = $this->reportService->deleteReport($report, $conference, $this->getUser());

        if ($exception) {
            return $this->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/{file_name}", name="report_file_download", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="200", description="Downloading the specified file")
     * @OA\Response(response="401", description="The request is unauthenticated",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(property="code", type="integer", example=401),
     *           @OA\Property(property="message", type="string", example="JWT Token not found")
     *       )
     *  )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="404", description="The requested resource could not be found")
     * @OA\Response(response="500", description="Failed to open the file for reading")
     */
    public function download(string $file_name): StreamedResponse
    {
        return $this->reportService->downloadFile($file_name);
    }

    // TODO: move to base controller
    protected function saveData(
        FormInterface $form,
        Report        $report,
        Conference    $conference,
        UserInterface $user
    ): Response
    {
        $document = $form->get('document')->getData() ?? null;

        try {
            $report = $this->reportService->saveReportApi($report, $conference, $user, $document);
        } catch (\Exception $exception) {
            return $this->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        $errors = $this->reportService->getFormErrors($form);

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($report, Response::HTTP_CREATED, [], ['groups' => ['api_reports_store']]);
    }
}
