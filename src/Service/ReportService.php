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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportService extends BaseService
{
    protected FormFactoryInterface $formFactory;
    protected ReportRepository $reportRepository;
    protected FileUploader $fileUploader;
    protected ConferenceService $conferenceService;
    protected EntityManagerInterface $entityManager;
    protected FlashBagInterface $flashBag;

    public function __construct(
        FormFactoryInterface   $formFactory,
        ReportRepository       $reportRepository,
        FileUploader           $fileUploader,
        ConferenceService      $conferenceService,
        EntityManagerInterface $entityManager,
        FlashBagInterface      $flashBag
    )
    {
        $this->formFactory = $formFactory;
        $this->reportRepository = $reportRepository;
        $this->fileUploader = $fileUploader;
        $this->conferenceService = $conferenceService;
        $this->entityManager = $entityManager;
        $this->flashBag = $flashBag;
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
    ): ?Report
    {
        if ($document) {
            $reportId = $this->entityManager->contains($report) ? $report->getId() : null;

            if ($reportId) {
                $fileExist = $this->reportRepository->fileNameExist($reportId);
                $this->deleteUploadedFile(array_shift($fileExist));
            }

            $documentName = $this->fileUploader->upload($document);

            if (!$documentName) {
                $this->flashBag->add(
                    'file-error',
                    'File upload error. Try again later.'
                );

                return null;
            }

            $report->setDocument($documentName);
        }

        $userPartOfConference = $this->conferenceService->findParticipantByUserId($user->getId(), $conference->getId());

        if (!$userPartOfConference) {
            $conference->addUser($user);
        }

        $report->setUser($user);
        $report->setConference($conference);
        $this->reportRepository->saveData($report);

        return $report;
    }

    public function saveReportApi(
        Report        $report,
        Conference    $conference,
        UserInterface $user,
        ?UploadedFile $document = null
    ): Report
    {
        if ($document) {

            $reportId = $this->entityManager->contains($report) ? $report->getId() : null;

            if ($reportId) {
                $fileExist = $this->reportRepository->fileNameExist($reportId);
                $this->deleteUploadedFile(array_shift($fileExist));
            }

            $documentName = $this->fileUploader->upload($document);

            if (!$documentName) {
                throw new \RuntimeException("File upload failed due to server error.", 500);
            }

            $report->setDocument($documentName);
        }

        $userPartOfConference = $this->conferenceService->findParticipantByUserId($user->getId(), $conference->getId());

        if (!$userPartOfConference) {
            $conference->addUser($user);
        }

        $report->setUser($user);
        $report->setConference($conference);
        $this->reportRepository->saveData($report);

        return $report;
    }

    public function deleteReport(Report $report, Conference $conference, UserInterface $user): ?\Exception
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
        $filePath = $this->fileUploader->getTargetDirectory() . '/' . $fileName;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new NotFoundHttpException('File not found or unavailable');
        }

        $response = new StreamedResponse(function () use ($filePath) {
            $stream = fopen($filePath, 'r');

            if ($stream === false) {
                throw new \RuntimeException('Failed to open the file for reading');
            }

            while (!feof($stream)) {
                echo fread($stream, 1024);
                flush();
            }

            fclose($stream);
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }

    public function downloadFileApi(string $fileName): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($fileName) {
            $file = $this->fileUploader->getTargetDirectory() . '/' . $fileName;

            if (!file_exists($file)) {
                throw $this->createNotFoundException('File not found.');
            }

            $stream = fopen($file, 'r');

            while (!feof($stream)) {
                echo fread($stream, 1024);
                flush();
            }

            fclose($stream);
        });

        // Встановлення заголовків для завантаження файлу
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }

    public function getAllReportsWithFilters(Conference $conference, array $filters = []): array
    {
        return $this->reportRepository->getAllReportsWithFilters($conference, $filters);
    }

    public function prepareReportFilters(array $filters, Conference $conference): array
    {
        if ($filters['start_time']->format('Y-m-d H:i:s') === $filters['end_time']->format('Y-m-d H:i:s')) {
            $filters['start_time'] = null;
            $filters['end_time'] = null;

            return $filters;
        }

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
