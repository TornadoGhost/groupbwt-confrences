<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Import\Csv\ConferencesCsv;
use App\Message\ImportNewConferencesCsv;
use App\Service\ConferenceService;
use App\Service\Export;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1/conferences", name="api_")
 */
class ConferenceController extends AbstractController
{
    private ConferenceService $conferenceService;
    private Export $export;

    public function __construct(
        ConferenceService $conferenceService,
        Export            $export
    )
    {
        $this->conferenceService = $conferenceService;
        $this->export = $export;
    }

    /**
     * @Route("/", name="conferences_index", methods={"GET"})
     *
     * @OA\Parameter(
     *      name="page",
     *      in="query",
     *      description="Pagination page",
     *      @OA\Schema(type="integer"),
     *      example="1")
     * @OA\Parameter(
     *      name="report_number",
     *      in="query",
     *      description="The number of reports in every conferences",
     *      @OA\Schema(type="integer"),
     *      example="7")
     * @OA\Parameter(
     *       name="start_date",
     *       in="query",
     *       description="The start date and time of the conferences",
     *       @OA\Schema(type="date_time"),
     *       example="2024-12-27T11:00")
     * @OA\Parameter(
     *        name="end_date",
     *        in="query",
     *        description="The end date and time of the conferences",
     *        @OA\Schema(type="date_time"),
     *        example="2024-12-27T18:00")
     * @OA\Parameter(
     *         name="is_available",
     *         in="query",
     *         description="Show conference if it is avaiable minimum 15 minutes for a report",
     *         @OA\Schema(type="boolean"),
     *         example="true")
     * @OA\Response(response="200", description="Got paginated conferences",
     *     @OA\JsonContent(
     *          type="object",
     *          @OA\Property(
     *              property="data",
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example=364),
     *                  @OA\Property(property="title", type="string", example="test"),
     *                  @OA\Property(property="startedAt", type="string", format="datetime", example="2025-10-01 11:00"),
     *                  @OA\Property(property="endedAt", type="string", format="datetime", example="2025-10-01 18:00")
     *              )
     *          ),
     *          @OA\Property(property="total", type="integer", example=18),
     *          @OA\Property(property="count", type="integer", example=15),
     *          @OA\Property(property="current_page", type="integer", example=1),
     *          @OA\Property(property="first_page_url", type="string", format="url", example="/api/v1/conferences/?page=1"),
     *          @OA\Property(property="last_page", type="integer", example=2),
     *          @OA\Property(property="last_page_url", type="string", format="url", example="/api/v1/conferences/?page=2"),
     *          @OA\Property(property="next_page_url", type="string", format="url", example="/api/v1/conferences/?page=2"),
     *          @OA\Property(property="path", type="string", example="/api/v1/conferences/"),
     *          @OA\Property(property="per_page", type="integer", example=15),
     *          @OA\Property(property="prev_page_url", type="string", format="url", example=null),
     *          @OA\Property(property="to", type="integer", example=15)))
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="500", description="Server error")
     */
    public function index(Request $request): Response
    {
        $userId = !$this->getUser() ? null : $this->getUser()->getId();
        $conferences = $this->conferenceService->getAllConferencesWithFiltersPaginateApi(
            ConferenceService::COUNT_PER_PAGE,
            $request->query->getInt('page', 1),
            $userId,
            $request->query->all()
        );

        return $this->json($conferences, Response::HTTP_OK, [], ['groups' => ['api_conferences_all']]);
    }

    /**
     * @Route("", name="conferences_store", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @OA\RequestBody(required=true,
     *      @OA\JsonContent(
     *          type="object",
     *          required={"title", "country", "latitude", "longitude", "startedAt", "endedAt"},
     *          @OA\Property(property="title", type="string", example="Title"),
     *          @OA\Property(property="country", type="string", example="UA"),
     *          @OA\Property(property="latitude", type="float", example="49.456206"),
     *          @OA\Property(property="longitude", type="float", example="26.275986"),
     *          @OA\Property(property="startedAt", type="string", format="date-time", example="2025-10-01T11:00"),
     *          @OA\Property(property="endedAt", type="string", format="date-time", example="2025-10-01T18:00")))
     *
     * @OA\Response(response="201", description="Created a new conference",
     *      @OA\JsonContent(type="object", ref=@Model(type=Conference::class, groups={"api_conferences_store"})))
     * @OA\Response(response="422", description="Form validations errors",
     *       @OA\JsonContent(
     *           type="object",
     *           @OA\Property(
     *               property="errors",
     *               type="object",
     *               @OA\Property(property="title", type="string", example={"The title should be not null"}),
     *               @OA\Property(property="startedAt", type="string", example={"The start date cannot be blank"}),
     *               @OA\Property(property="endedAt", type="string", example={"The start date cannot be blank"}),
     *               @OA\Property(property="latitude", type="string", example={"The latitude should be not null"}),
     *               @OA\Property(property="longitude", type="string", example={"The longitude should be not null"}),
     *               @OA\Property(property="country", type="string", example={"Please select a country"})))))
     * @OA\Response(response="401", description="The request is unauthenticated",
     *      @OA\JsonContent(
     *          type="object",
     *          @OA\Property(property="code", type="integer", example=401),
     *          @OA\Property(property="message", type="string", example="JWT Token not found")
     *      )
     * )
     * @OA\Response(response="403", description="The user doesn't have permissions to a resource or action")
     * @OA\Response(response="500", description="Server error")
     */
    public function store(Request $request): Response
    {
        $conference = new Conference();
        $requestData = $request->toArray();
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->submit($requestData);

        if ($form->isSubmitted() && $form->isValid()) {
            $conference = $this->conferenceService->saveFormChanges($conference,
                [
                    'latitude' => $requestData['latitude'] ?? null,
                    'longitude' => $requestData['longitude'] ?? null,
                ]
            );

            return $this->json($conference, Response::HTTP_CREATED, [], ['groups' => ['api_conferences_store']]);
        }

        $errors = $this->conferenceService->getFormErrors($form);

        return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="conferences_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="200", description="Showed the specified conference",
     *      @OA\JsonContent(type="object", ref=@Model(type=Conference::class, groups={"api_conferences_show"})))
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
    public function show(Conference $conference): Response
    {
        return $this->json($conference, Response::HTTP_OK, [], ['groups' => ['api_conferences_show']]);
    }

    /**
     * @Route("/{id}", name="conferences_update", methods={"PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     *
     * @OA\Response(response="200", description="Updated the specified conference",
     *     @OA\JsonContent(type="object", ref=@Model(type=Conference::class, groups={"api_conferences_show"})))
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
    public function update(Request $request, Conference $conference): Response
    {
        $form = $this->createForm(ConferenceType::class, $conference);
        $form->submit($request->toArray());

        if ($form->isSubmitted() && $form->isValid()) {
            $conference = $this->conferenceService->saveFormChanges($form, $conference);

            return $this->json($conference, Response::HTTP_OK, [], ['groups' => ['api_conferences_show']]);
        }

        return $this->json([
            'errors' => $this->conferenceService->getFormErrors($form)
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/{id}", name="conferences_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @OA\Response(response="204", description="Deleted the specified conference")
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
    public function delete(Conference $conference): Response
    {
        $this->conferenceService->delete($conference);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/join", name="conferences_join", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="204", description="Joined the specified conference")
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
    public function join(Conference $conference, ConferenceService $conferenceService): Response
    {
        $conferenceService->addUserToConference($conference, $this->getUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/cancel", name="conferences_cancel", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     *
     * @OA\Response(response="204", description="Canceled the specified conference")
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
    public function cancel(Conference $conference): Response
    {
        $this->conferenceService->removeUserFromConference($conference, $this->getUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/export-excel", name="conferences_export_excel", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function exportExcel(Conference $conference): Response
    {
        return $this->export->exportExcel(
            $this->conferenceService->formatForExcel($conference),
            'conference_' . $conference->getStartedAt()->format('d-m-Y'));
    }

    /**
     * @Route("/{id}/export-pdf", name="conferences_export_pdf", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function exportPdf(Conference $conference): Response
    {
        return $this->export->exportPdf(
            $this->conferenceService->formatForPdf($conference),
            'pdf/conferenceSchedule.html.twig',
            'conference_' . $conference->getStartedAt()->format('d-m-Y')
        );
    }

    /**
     * @Route("/import-csv", name="conferences_import_csv", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function importCsv(Request $request, MessageBusInterface $bus, ConferencesCsv $import): Response
    {
        if (!$request->files->get('import_csv')) {
            throw new BadRequestHttpException('File is not found.');
        }

        $bus->dispatch(
            new ImportNewConferencesCsv(
                $import->getCsvData($request->files->get('import_csv')->getPathname()),
                $this->getUser()
            )
        );

        return $this->json('Successfully pushed to queue');
    }
}
