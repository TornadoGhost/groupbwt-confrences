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
        int $conferenceId,
        \DateTimeInterface $startedAt,
        \DateTimeInterface $endedAt
    ): FormInterface
    {
        $form = $this->formFactory->create(ReportType::class, $report, [
            'conference_id' => $conferenceId,
            'conference_start' => $startedAt,
            'conference_end' => $endedAt
        ]);
        $form->handleRequest($request);

        return $form;
    }

    public function saveData(object $entity): void
    {
        $this->reportRepository->saveData($entity);
    }

    public function getAvailableTimeForReport(int $conferenceId): QueryBuilder
    {
        return $this->reportRepository->getAvailableTimeForReport($conferenceId);
    }

    public function saveReportWithFile(Report $report, UploadedFile $presentationFile, Conference $conference)
    {
        if ($presentationFile) {
            $originalFilename = pathinfo($presentationFile->getClientOriginalName(), PATHINFO_FILENAME);

            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$presentationFile->guessExtension();

            $directory = $this->parameterBag->get('kernel.project_dir').'/public/uploads/reports';
            $filesystem = new Filesystem();
            if (!$filesystem->exists($directory)) {
                $filesystem->mkdir($directory);
            }

            try {
                $presentationFile->move($directory, $newFilename);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $report->setDocument($newFilename);
        }

        $report->setConference($conference);
        $this->reportRepository->saveData($report);
    }
}
