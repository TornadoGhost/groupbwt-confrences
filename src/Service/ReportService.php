<?php

namespace App\Service;

use App\Entity\Conference;
use App\Entity\Report;
use App\Form\ReportType;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportService
{
    protected FormFactoryInterface $formFactory;
    protected ReportRepository $reportRepository;
    protected FileUploader $fileUploader;
    protected ConferenceService $conferenceService;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        FormFactoryInterface   $formFactory,
        ReportRepository       $reportRepository,
        FileUploader           $fileUploader,
        ConferenceService      $conferenceService,
        EntityManagerInterface $entityManager
    )
    {
        $this->formFactory = $formFactory;
        $this->reportRepository = $reportRepository;
        $this->fileUploader = $fileUploader;
        $this->conferenceService = $conferenceService;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array<Report>
     */
    public function getAllReports(): array
    {
        return $this->reportRepository->findAll();
    }

    public function prepareForm(
        Report     $report,
        Request    $request,
        Conference $conference
    ): FormInterface
    {
        $form = $this->formFactory->create(ReportType::class, $report, [
            'conference_id' => $conference->getId(),
            'conference_start' => $conference->getStartedAt(),
            'conference_end' => $conference->getEndedAt()
        ]);
        $form->handleRequest($request);

        return $form;
    }

    public function saveReport(
        Report        $report,
        Conference    $conference,
        UserInterface $user,
        ?UploadedFile $document = null
    ): bool
    {
        if ($document) {
            $reportId = $this->entityManager->contains($report) ? $report->getId() : null;

            if ($reportId) {
                $fileExist = $this->reportRepository->fileNameExist($reportId);
                $this->deleteUploadedFile(array_shift($fileExist));
            }

            $documentName = $this->fileUploader->upload($document);

            if (!$documentName) {
                return false;
            }

            $report->setDocument($documentName);
        }

        $userId = $user->getId();
        $conferenceId = $conference->getId();
        $userPartOfConference = $this->conferenceService->findParticipantByUserId($userId, $conferenceId);

        if (!$userPartOfConference) {
            $conference->addUser($user);
        }

        $report->setUser($user);
        $report->setConference($conference);
        $this->reportRepository->saveData($report);

        return true;
    }

    public function deleteReport(Report $report, Conference $conference, UserInterface $user): ?string
    {
        return $this->reportRepository->deleteReport($report, $conference, $user);
    }

    public function deleteUploadedFile(string $fileName): void
    {
        $filesystem = new Filesystem();
        $filePath = $this->fileUploader->getTargetDirectory() . '/' . $fileName;

        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Report
    {
        return $this->reportRepository->findOneBy($criteria, $orderBy);
    }

    public function downloadFile(string $fileName): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($fileName) {
            $file = $this->fileUploader->getTargetDirectory() . '/' . $fileName;
            $stream = fopen($file, 'r');

            while (!feof($stream)) {
                echo fread($stream, 1024);
                flush();
            }

            fclose($stream);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");

        return $response;
    }

    public function getAllReportsWithFilters(Conference $conference, array $filters = []): array
    {
        return $this->reportRepository->getAllReportsWithFilters($conference, $filters);
    }

    public function prepareReportFilters(array $filters, Conference $conference): array
    {
        if ($filters['start_time']) {
            $filters['start_time'] = (new \DateTime($conference->getStartedAt()->format('Y-m-d')))->setTime(
                $filters['start_time']->format('H'),
                $filters['start_time']->format('i'),
                $filters['start_time']->format('s')
            );
        }

        if ($filters['end_time']) {
            $filters['end_time'] = (new \DateTime($conference->getEndedAt()->format('Y-m-d')))->setTime(
                $filters['end_time']->format('H'),
                $filters['end_time']->format('i'),
                $filters['end_time']->format('s')
            );
        }

        return $filters;
    }
}
