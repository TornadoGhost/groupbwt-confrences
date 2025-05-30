<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\IndexReportRequest;
use App\DTO\Request\ReportRequest;
use App\Entity\Conference;
use App\Entity\Report;
use App\Message\ConferenceEmailNotification;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportService extends BaseService
{
    protected ReportRepository $reportRepository;
    protected FileUploader $fileUploader;
    protected ConferenceService $conferenceService;
    protected EntityManagerInterface $entityManager;
    protected FlashBagInterface $flashBag;
    protected Filesystem $filesystem;
    private MessageBusInterface $bus;

    public function __construct(
        ReportRepository       $reportRepository,
        FileUploader           $fileUploader,
        ConferenceService      $conferenceService,
        EntityManagerInterface $entityManager,
        FlashBagInterface      $flashBag,
        Filesystem             $filesystem,
        MessageBusInterface    $bus
    )
    {
        $this->reportRepository = $reportRepository;
        $this->fileUploader = $fileUploader;
        $this->conferenceService = $conferenceService;
        $this->entityManager = $entityManager;
        $this->flashBag = $flashBag;
        $this->filesystem = $filesystem;
        $this->bus = $bus;
    }

    public function save(
        Report        $report,
        Conference    $conference,
        UserInterface $user,
        ?UploadedFile $document = null,
        bool          $api = false
    ): ?Report
    {
        if ($document) {
            $reportId = $this->entityManager->contains($report) ? $report->getId() : null;

            if ($reportId) {
                $fileExist = $this->reportRepository->fileNameExist($reportId);

                if ($fileExist) {
                    $this->deleteUploadedFile(array_shift($fileExist));
                }
            }

            $documentName = $this->fileUploader->upload($document);

            if (!$documentName) {
                if ($api) {
                    throw new \RuntimeException("File upload failed due to server error.", 500);
                }
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

    public function deleteReport(Report $report, Conference $conference): ?\Exception
    {
        return $this->reportRepository->deleteReport($report, $conference);
    }

    public function deleteUploadedFile(string $fileName): void
    {
        $filePath = $this->fileUploader->getTargetDirectory() . '/' . $fileName;

        if ($this->filesystem->exists($filePath)) {
            $this->filesystem->remove($filePath);
        }
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?Report
    {
        return $this->reportRepository->findOneBy($criteria, $orderBy);
    }

    public function downloadFile(string $fileName): StreamedResponse
    {
        $filePath = $this->fileUploader->getTargetDirectory() . '/' . $fileName;

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('File not found');
        } else {
            $filePerms = fileperms($filePath);

            if (!(($filePerms & 0x0100) === 0x0100)
                ||
                !(($filePerms & 0x0020) === 0x0020)
                ||
                !(($filePerms & 0x0004) === 0x0004)) {
                throw new NotFoundHttpException('File unavailable');
            }
        }

        $response = new StreamedResponse(function () use ($filePath) {
            $stream = fopen($filePath, 'r');

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

    public function getAllReportsWithFilters(Conference $conference, IndexReportRequest $request): array
    {
        return $this->reportRepository->getAllReportsWithFilters($conference, $request);
    }

    public function prepareReportFilters(array $filters, Conference $conference): array
    {
        if (
            (!empty($filters['start_time']) && !empty($filters['end_time']))
            &&
            ($filters['start_time']->format('Y-m-d H:i:s') === $filters['end_time']->format('Y-m-d H:i:s'))
        ) {
            $filters['start_time'] = null;
            $filters['end_time'] = null;

            return $filters;
        }

        if (!empty($filters['start_time'])) {
            $filters['start_time'] = (new \DateTime($conference->getStartedAt()->format('Y-m-d')))->setTime(
                (int)$filters['start_time']->format('H'),
                (int)$filters['start_time']->format('i'),
                (int)$filters['start_time']->format('s')
            );
        }

        if (!empty($filters['end_time'])) {
            $filters['end_time'] = (new \DateTime($conference->getEndedAt()->format('Y-m-d')))->setTime(
                (int)$filters['end_time']->format('H'),
                (int)$filters['end_time']->format('i'),
                (int)$filters['end_time']->format('s')
            );
        }

        return $filters;
    }

    public function sendEmailToAdmin(string $userEmail, Report $report, Conference $conference): void
    {
        $this->bus->dispatch(new ConferenceEmailNotification(
            'admin@myconference.com',
            'User participated in conference',
            'User - ' . $userEmail . ' created report - ' . $report->getTitle() . '(' . $report->getId() .')'
            . ' for conference - ' . $conference->getTitle() . '(' . $conference->getId() .')'
        ));
    }

    public function setReportData(Report $report, ReportRequest $request): Report
    {
        $report->setTitle($request->getTitle());
        $report->setDescription($request->getDescription());
        $report->setStartedAt(new \DateTime($request->getStartedAt()));
        $report->setEndedAt(new \DateTime($request->getEndedAt()));

        return $report;
    }

    public function createReport(ReportRequest $request): Report
    {
        $report = new Report();

        return $this->setReportData($report, $request);
    }
}
