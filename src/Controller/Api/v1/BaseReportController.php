<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Entity\Report;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseReportController extends AbstractController
{
    protected ReportService $reportService;

    public function __construct(
        ReportService     $reportService
    )
    {
        $this->reportService = $reportService;
    }

    protected function saveData(
        Report        $report,
        Conference    $conference,
        UserInterface $user,
        ?UploadedFile $document
    ): Response
    {
        try {
            $report = $this->reportService->save($report, $conference, $user, $document, true);
        } catch (\Exception $exception) {
            return $this->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        $this->reportService->sendEmailToAdmin($user->getEmail(), $report, $conference);

        return $this->json($report, Response::HTTP_CREATED, [], ['groups' => ['api_reports_store']]);
    }
}
