<?php

namespace App\Service;

use App\Entity\Report;
use App\Entity\ReportComment;
use App\Form\ReportCommentType;
use App\Repository\ReportCommentRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReportCommentService
{
    protected ReportCommentRepository $reportCommentRepository;
    protected FormFactoryInterface $formFactory;

    public function __construct(
        ReportCommentRepository $reportCommentRepository,
        FormFactoryInterface    $formFactory,
        FlashBagInterface       $flashBag
    )
    {
        $this->reportCommentRepository = $reportCommentRepository;
        $this->formFactory = $formFactory;
    }

    public function getAllCommentsByReportId(int $reportId): ?array
    {
        return $this->reportCommentRepository->getAllCommentsByReportId($reportId);
    }

    public function createReportComment(
        Request        $request,
        UserInterface  $user,
        Report         $report,
        ?ReportComment $comment = null
    ): ?FormInterface
    {
        if (!$comment) {
            $comment = new ReportComment();
        }

        $commentForm = $this->formFactory->create(ReportCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setUser($user);
            $comment->setReport($report);
            $this->reportCommentRepository->add($comment, true);

            return null;
        }

        return $commentForm;
    }

    public function updateComment(
        Request        $request,
        ?ReportComment $comment
    ): ?FormInterface
    {
        $commentForm = $this->formFactory->create(ReportCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->reportCommentRepository->add($comment, true);

            return null;
        }

        return $commentForm;
    }

    public function removeComment(ReportComment $comment, bool $flush): void
    {
        $this->reportCommentRepository->remove($comment, $flush);
    }
}
