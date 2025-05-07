<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\DTO\Request\IndexConferenceRequest;
use App\DTO\Request\ConferenceRequest;
use App\Entity\Conference;
use App\Form\ConferenceType;
use App\Import\Csv\ConferencesCsv;
use App\Message\ImportNewConferencesCsv;
use App\Service\ConferenceService;
use App\Service\Export;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conferences", name="api_", requirements={"id"="\d+"})
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
     * @Route("", name="conferences_index", methods={"GET"})
     */
    public function index(IndexConferenceRequest $request): Response
    {
        return $this->json(
            $this->conferenceService->getConferences($request, $this->getUser()),
            Response::HTTP_OK,
            [],
            ['groups' => ['api_conferences_all']]
        );
    }

    /**
     * @Route("", name="conferences_store", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function store(ConferenceRequest $request): Response
    {
        return $this->json(
            $this->conferenceService->createConference($request),
            Response::HTTP_CREATED,
            [],
            ['groups' => ['api_conferences_store']]
        );
    }

    /**
     * @Route("/{id}", name="conferences_show", methods={"GET"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(Conference $conference): Response
    {
        return $this->json(
            $conference,
            Response::HTTP_OK,
            [],
            ['groups' => ['api_conferences_show']]);
    }

    /**
     * @Route("/{id}", name="conferences_update", methods={"PUT"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function update(ConferenceRequest $request, Conference $conference): Response
    {
        return $this->json(
            $this->conferenceService->updateConference($conference, $request),
            Response::HTTP_OK,
            [],
            ['groups' => ['api_conferences_store']]
        );
    }

    /**
     * @Route("/{id}", name="conferences_delete", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function delete(Conference $conference): Response
    {
        $this->conferenceService->delete($conference);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/join", name="conferences_join", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function join(Conference $conference, ConferenceService $conferenceService): Response
    {
        $conferenceService->addUserToConference($conference, $this->getUser());

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/cancel", name="conferences_cancel", methods={"POST"})
     * @Security("is_granted('ROLE_USER')")
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

    /**
     * @Route("/subscribed", name="conferences_user", methods={"GET"}, priority="1")
     * @Security("is_granted('ROLE_USER')")
     */
    public function subscribed(): Response
    {
        $usersSubscribedConferences = $this->getUser()->getConferences();

        return $this->json($usersSubscribedConferences, Response::HTTP_OK, [], ['groups' => ['api_conferences_subscribed']]);
    }
}
