<?php

namespace App\Service;

use App\Entity\Conference;
use App\Entity\Report;
use App\Form\ReportType;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ReportService
{
    protected FormFactoryInterface $formFactory;
    protected ReportRepository $reportRepository;
    protected SluggerInterface $slugger;
    protected ParameterBagInterface $parameterBag;
    protected FileUploader $fileUploader;
    protected ConferenceService $conferenceService;
    protected EntityManagerInterface $entityManager;

    public function __construct(
        FormFactoryInterface $formFactory,
        ReportRepository $reportRepository,
        SluggerInterface $slugger,
        ParameterBagInterface $parameterBag,
        FileUploader $fileUploader,
        ConferenceService $conferenceService,
        EntityManagerInterface $entityManager
    )
    {
        $this->formFactory = $formFactory;
        $this->reportRepository = $reportRepository;
        $this->slugger = $slugger;
        $this->parameterBag = $parameterBag;
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
        Report $report,
        Request $request,
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

    public function getAvailableTimeForReport(int $conferenceId): QueryBuilder
    {
        return $this->reportRepository->getAvailableTimeForReport($conferenceId);
    }

    public function saveReportWithFile(
        Report $report,
        Conference $conference,
        UserInterface $user,
        ?UploadedFile $document = null
    ): bool
    {
        if ($document) {
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
        $fileName = $report->getDocument();
        $this->entityManager->beginTransaction();

        try {
            $this->deleteUploadedFile($fileName);
            $this->reportRepository->deleteReport($report);
            $this->conferenceService->removeUserFromConference($conference, $user);
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            return $e->getMessage();
        }

        return null;
    }

    public function deleteUploadedFile($fileName): void
    {
        $filesystem = new Filesystem();
        $filePath = $this->fileUploader->getTargetDirectory() . '/' . $fileName ;

        if ($filesystem->exists($filePath)) {
            $filesystem->remove($filePath);
        }
    }
}
