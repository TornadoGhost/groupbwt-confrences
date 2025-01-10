<?php

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Entity\Report;
use App\Form\ReportType;
use App\Service\ConferenceService;
use App\Service\ReportService;
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
     */
    public function index(
        Conference    $conference,
        ReportService $reportService
    ): Response
    {
        $reports = $reportService->getAllReportsWithFilters($conference);

        return $this->json($reports, Response::HTTP_OK, [], ['groups' => ['api_reports_all']]);
    }

    /**
     * @Route("", name="reports_store", methods={"POST"})
     * @Security("is_granted('ROLE_ANNOUNCER')")
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
     */
    public function show(Report $report): Response
    {
        return $this->json($report, Response::HTTP_OK, [], ['groups' => ['api_reports_show']]);
    }

    /**
     * @Route("/{id}", name="reports_update", methods={"PUT"})
     * @IsGranted("EDIT", subject="report")
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
     * @Route("/{id}/{file_name}", name="app_report_file_download", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
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
            return $this->json(['errors' => $errors], 422);
        }

        return $this->json($report, 200, [], ['groups' => ['api_reports_store']]);
    }
}
