<?php

declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Entity\Conference;
use App\Entity\Report;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
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
        FormInterface $form,
        Report        $report,
        Conference    $conference,
        UserInterface $user
    ): Response
    {
        $document = $form->get('document')->getData() ?? null;

        try {
            $report = $this->reportService->save($report, $conference, $user, $document, true);
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
